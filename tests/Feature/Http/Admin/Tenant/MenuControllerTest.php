<?php

namespace Tests\Feature\Http\Admin\Tenant;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Location $location;

    protected Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'menu-ctrl-tenant']);
        $this->tenant->domains()->create(['domain' => 'menu-ctrl.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

        $this->location = Location::factory()->create(['tenant_id' => 'menu-ctrl-tenant']);
        $this->template = Template::factory()->create();

        // Unlimited plan so limit checks pass unless explicitly testing limits
        $plan = Plan::factory()->create([
            'max_locations' => 0,
            'max_menus_per_location' => 0,
            'max_products' => 0,
        ]);
        Subscription::create([
            'tenant_id' => 'menu-ctrl-tenant',
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
        return 'http://menu-ctrl.localhost'.route($routeName, $params, false);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Carta Principal',
            'description' => 'Descripción del menú',
            'location_id' => $this->location->id,
            'template_id' => $this->template->id,
            'is_active' => true,
            'show_prices' => true,
            'show_currency' => false,
            'show_calories' => false,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    #[Test]
    public function store_creates_menu_with_valid_data(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.menus.store', ['location' => $this->location->id]), $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('menus', [
            'name' => 'Carta Principal',
            'location_id' => $this->location->id,
            'tenant_id' => 'menu-ctrl-tenant',
        ]);
    }

    #[Test]
    public function store_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.menus.store', ['location' => $this->location->id]), $this->validPayload(['name' => '']));

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_requires_valid_location_id(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.menus.store', ['location' => $this->location->id]), $this->validPayload(['location_id' => 99999]));

        $response->assertSessionHasErrors('location_id');
    }

    #[Test]
    public function store_requires_valid_template_id(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.menus.store', ['location' => $this->location->id]), $this->validPayload(['template_id' => 99999]));

        $response->assertSessionHasErrors('template_id');
    }

    #[Test]
    public function store_returns_422_when_required_fields_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.menus.store', ['location' => $this->location->id]), []);

        $response->assertSessionHasErrors(['name', 'location_id', 'template_id']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    #[Test]
    public function update_modifies_menu_fields(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-ctrl-tenant',
            'location_id' => $this->location->id,
            'template_id' => $this->template->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.menus.update', ['menu' => $menu->id]), [
                'name' => 'Carta Actualizada',
                'description' => 'Nueva descripción',
                'template_id' => $this->template->id,
                'is_active' => false,
                'show_prices' => false,
                'show_currency' => true,
                'show_calories' => false,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Carta Actualizada',
            'is_active' => false,
        ]);
    }

    #[Test]
    public function update_returns_422_when_name_is_empty(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-ctrl-tenant',
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.menus.update', ['menu' => $menu->id]), [
                'name' => '',
                'template_id' => $this->template->id,
            ]);

        $response->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    #[Test]
    public function destroy_deletes_menu(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-ctrl-tenant',
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.menus.destroy', ['menu' => $menu->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    #[Test]
    public function destroy_returns_404_for_nonexistent_menu(): void
    {
        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.menus.destroy', ['menu' => 99999]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Plan limit on store
    // -------------------------------------------------------------------------

    #[Test]
    public function store_blocked_when_menu_plan_limit_reached(): void
    {
        Subscription::query()->delete();
        $limitedPlan = Plan::factory()->create([
            'max_locations' => 0,
            'max_menus_per_location' => 1,
            'max_products' => 0,
        ]);
        Subscription::create([
            'tenant_id' => 'menu-ctrl-tenant',
            'plan_id' => $limitedPlan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);

        Menu::factory()->create([
            'location_id' => $this->location->id,
            'tenant_id' => 'menu-ctrl-tenant',
        ]); // already at limit

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.locations.menus.store', ['location' => $this->location->id]), $this->validPayload(['name' => 'Segundo Menú']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('menus', 1);
    }
}
