<?php

namespace Tests\Feature\Http\Admin\Tenant;

use App\Models\Country;
use App\Models\Location;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'loc-ctrl-tenant']);
        $this->tenant->domains()->create(['domain' => 'loc-ctrl.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

        $this->country = Country::factory()->create();

        // Unlimited plan so limit checks pass unless explicitly testing limits
        $plan = Plan::factory()->create([
            'max_locations' => 0,
            'max_menus_per_location' => 0,
            'max_products' => 0,
        ]);
        Subscription::create([
            'tenant_id' => 'loc-ctrl-tenant',
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    protected function tenantUrl(string $routeName, array $params = []): string
    {
        return 'http://loc-ctrl.localhost'.route($routeName, $params, false);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Cafetería Test',
            'address' => 'Calle Mayor, 1',
            'city' => 'Madrid',
            'province' => 'Madrid',
            'postal_code' => '28001',
            'phone' => '+34 600 000 000',
            'country_id' => $this->country->id,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    #[Test]
    public function index_returns_200_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.locations.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->get($this->tenantUrl('tenant.locations.index'));

        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    #[Test]
    public function store_creates_location_with_valid_data(): void
    {
        $payload = $this->validPayload([
            'lang' => 'es',
            'currency' => 'EUR',
            'time_zone' => 'Europe/Madrid',
            'time_format' => 'H:i',
            'languages' => ['es'],
            'social_medias' => [],
            'latitude' => null,
            'longitude' => null,
            'description' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'name' => 'Cafetería Test',
            'tenant_id' => 'loc-ctrl-tenant',
        ]);

        // Session should have success, not error
        $response->assertSessionMissing('error');
    }

    #[Test]
    public function store_returns_422_when_name_is_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.store'), $this->validPayload(['name' => '']));

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_returns_422_when_country_is_invalid(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.store'), $this->validPayload(['country_id' => 99999]));

        $response->assertSessionHasErrors('country_id');
    }

    #[Test]
    public function store_returns_422_when_required_fields_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.store'), []);

        $response->assertSessionHasErrors(['name', 'address', 'city', 'province', 'postal_code', 'country_id']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    #[Test]
    public function update_modifies_location_fields(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'loc-ctrl-tenant']);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.locations.update', ['location' => $location->id]), [
                'name' => 'Nombre Actualizado',
                'address' => $location->address,
                'city' => 'Sevilla',
                'province' => $location->province,
                'postal_code' => $location->postal_code,
                'country_id' => $this->country->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Nombre Actualizado',
            'city' => 'Sevilla',
        ]);
    }

    #[Test]
    public function update_returns_422_when_name_is_empty(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'loc-ctrl-tenant']);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.locations.update', ['location' => $location->id]), [
                'name' => '',
                'address' => $location->address,
                'city' => $location->city,
                'province' => $location->province,
                'postal_code' => $location->postal_code,
                'country_id' => $this->country->id,
            ]);

        $response->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    #[Test]
    public function destroy_deletes_location(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'loc-ctrl-tenant']);

        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.locations.destroy', ['location' => $location->id]));

        $response->assertRedirect($this->tenantUrl('tenant.locations.index'));
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    #[Test]
    public function destroy_returns_404_for_nonexistent_location(): void
    {
        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.locations.destroy', ['location' => 99999]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Plan limit on store
    // -------------------------------------------------------------------------

    #[Test]
    public function store_blocked_when_plan_limit_reached(): void
    {
        // Override with a plan that allows only 1 location
        Subscription::query()->delete();
        $limitedPlan = Plan::factory()->create([
            'max_locations' => 1,
            'max_menus_per_location' => 0,
            'max_products' => 0,
        ]);
        Subscription::create([
            'tenant_id' => 'loc-ctrl-tenant',
            'plan_id' => $limitedPlan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);

        Location::factory()->create(['tenant_id' => 'loc-ctrl-tenant']); // already at limit

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.store'), $this->validPayload(['name' => 'Segunda']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('locations', 1);
    }
}
