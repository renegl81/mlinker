<?php

namespace Tests\Feature\Plan;

use App\Actions\Plan\CheckLimit;
use App\Exceptions\PlanLimitExceededException;
use App\Models\Country;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cached free plan to avoid contamination between tests
        Cache::forget('plan:free');

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function makePlan(array $overrides = []): Plan
    {
        $defaults = [
            'slug' => 'plan-'.uniqid(),
            'max_locations' => 1,
            'max_menus_per_location' => 1,
            'max_products' => 25,
            'max_images' => 0,
        ];

        return Plan::factory()->create(array_merge($defaults, $overrides));
    }

    private function subscribeTo(Plan $plan): void
    {
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);
    }

    private function tenantUrl(string $routeName, array $params = []): string
    {
        return 'http://test.localhost'.route($routeName, $params, false);
    }

    // -------------------------------------------------------------------------
    // CheckLimit action: unlimited (max = 0)
    // -------------------------------------------------------------------------

    #[Test]
    public function check_limit_allows_when_max_is_zero_unlimited(): void
    {
        $plan = $this->makePlan(['max_locations' => 0]);
        $this->subscribeTo($plan);

        Location::factory()->count(10)->create();

        $this->assertTrue((new CheckLimit)->execute('locations'));
    }

    // -------------------------------------------------------------------------
    // CheckLimit action: locations
    // -------------------------------------------------------------------------

    #[Test]
    public function check_limit_returns_false_when_locations_at_limit(): void
    {
        $plan = $this->makePlan(['max_locations' => 1]);
        $this->subscribeTo($plan);

        Location::factory()->create();

        $this->assertFalse((new CheckLimit)->execute('locations'));
    }

    #[Test]
    public function check_limit_throws_exception_when_locations_at_limit_and_throw_true(): void
    {
        $plan = $this->makePlan(['max_locations' => 1]);
        $this->subscribeTo($plan);

        Location::factory()->create();

        $this->expectException(PlanLimitExceededException::class);

        (new CheckLimit)->execute('locations', throw: true);
    }

    // -------------------------------------------------------------------------
    // CheckLimit action: menus (uses max_menus_per_location as total cap)
    // -------------------------------------------------------------------------

    #[Test]
    public function check_limit_returns_false_when_menus_at_limit(): void
    {
        $plan = $this->makePlan(['max_menus_per_location' => 1]);
        $this->subscribeTo($plan);

        $location = Location::factory()->create();
        Menu::factory()->create(['location_id' => $location->id]);

        $this->assertFalse((new CheckLimit)->execute('menus'));
    }

    #[Test]
    public function check_limit_returns_true_when_menus_below_limit(): void
    {
        $plan = $this->makePlan(['max_menus_per_location' => 3]);
        $this->subscribeTo($plan);

        $location = Location::factory()->create();
        Menu::factory()->count(2)->create(['location_id' => $location->id]);

        $this->assertTrue((new CheckLimit)->execute('menus'));
    }

    // -------------------------------------------------------------------------
    // HTTP: Free plan (1 location limit)
    // -------------------------------------------------------------------------

    #[Test]
    public function free_plan_tenant_cannot_create_second_location_via_http(): void
    {
        $plan = $this->makePlan(['max_locations' => 1]);
        $this->subscribeTo($plan);

        Location::factory()->create(); // already at limit

        $country = Country::factory()->create();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.store'), [
                'name' => 'Second Location',
                'address' => '123 Street',
                'city' => 'City',
                'province' => 'Province',
                'postal_code' => '12345',
                'phone' => '+34 600 000 000',
                'country_id' => $country->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('locations', 1);
    }

    // -------------------------------------------------------------------------
    // HTTP: Pro plan allows up to 3 locations
    // -------------------------------------------------------------------------

    #[Test]
    public function pro_plan_tenant_can_create_up_to_three_locations(): void
    {
        $plan = $this->makePlan(['max_locations' => 3]);
        $this->subscribeTo($plan);

        Location::factory()->count(2)->create();

        // Still within limit — CheckLimit returns true
        $this->assertTrue((new CheckLimit)->execute('locations'));
    }

    #[Test]
    public function pro_plan_tenant_cannot_create_fourth_location(): void
    {
        $plan = $this->makePlan(['max_locations' => 3]);
        $this->subscribeTo($plan);

        Location::factory()->count(3)->create();

        $this->assertFalse((new CheckLimit)->execute('locations'));
    }

    // -------------------------------------------------------------------------
    // Registration creates Free subscription
    // -------------------------------------------------------------------------

    #[Test]
    public function registration_creates_subscription_with_free_plan(): void
    {
        tenancy()->end();

        Plan::updateOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'slug' => 'free',
            'price' => '0.00',
            'period' => 'month',
            'description' => 'Free plan',
            'is_active' => true,
            'max_locations' => 1,
            'max_menus_per_location' => 1,
            'max_products' => 25,
            'max_images' => 0,
            'has_analytics' => false,
            'has_custom_qr' => false,
            'has_multilang' => false,
            'has_api_access' => false,
            'has_custom_domain' => false,
            'show_branding' => true,
            'trial_days' => 0,
            'sort_order' => 0,
        ]);

        Cache::forget('plan:free');

        $this->post(route('register.store'), [
            'name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'tenant_name' => 'Jane Restaurant',
            'tenant_domain' => 'jane-restaurant',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $freePlan = Plan::where('slug', 'free')->first();

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => 'jane-restaurant',
            'plan_id' => $freePlan->id,
            'type' => 'default',
            'stripe_status' => 'free',
        ]);
    }
}
