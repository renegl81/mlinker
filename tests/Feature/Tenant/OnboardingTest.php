<?php

namespace Tests\Feature\Tenant;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }

    protected function tenantUrl(string $routeName, array $params = []): string
    {
        $path = route($routeName, $params, false);

        return 'http://test.localhost'.$path;
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get($this->tenantUrl('tenant.onboarding.show'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function tenant_without_onboarding_is_redirected_to_wizard_when_accessing_dashboard(): void
    {
        $this->assertNull($this->tenant->onboarding_completed_at);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.dashboard'));

        $response->assertRedirect($this->tenantUrl('tenant.onboarding.show'));
    }

    #[Test]
    public function tenant_with_completed_onboarding_can_access_dashboard(): void
    {
        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.dashboard'));

        $response->assertStatus(200);
    }

    #[Test]
    public function onboarding_wizard_shows_current_step(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.onboarding.show'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/tenant/onboarding/Wizard')
            ->where('step', 0)
        );
    }

    #[Test]
    public function store_location_creates_location_and_advances_to_step_2(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.location'), [
                'name' => 'Cafetería Test',
                'address' => 'Calle Mayor, 1',
                'city' => 'Madrid',
                'phone' => '+34 600 000 000',
            ]);

        $response->assertRedirect($this->tenantUrl('tenant.onboarding.show'));

        $this->assertDatabaseHas('locations', [
            'name' => 'Cafetería Test',
            'city' => 'Madrid',
            'tenant_id' => 'test-tenant',
        ]);

        $this->tenant->refresh();
        $this->assertEquals(2, $this->tenant->onboarding_step);
    }

    #[Test]
    public function store_location_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.location'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_menu_creates_menu_and_advances_to_step_3(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        Template::factory()->create(['component_name' => 'Basic', 'is_active' => true]);

        $this->tenant->onboarding_step = 2;
        $this->tenant->save();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.menu'), [
                'name' => 'Carta de Verano',
                'description' => 'Menú especial de verano',
                'location_id' => $location->id,
            ]);

        $response->assertRedirect($this->tenantUrl('tenant.onboarding.show'));

        $this->assertDatabaseHas('menus', [
            'name' => 'Carta de Verano',
            'location_id' => $location->id,
            'tenant_id' => 'test-tenant',
        ]);

        $this->tenant->refresh();
        $this->assertEquals(3, $this->tenant->onboarding_step);
    }

    #[Test]
    public function store_menu_requires_name_and_location(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.menu'), [
                'name' => '',
                'location_id' => 999,
            ]);

        $response->assertSessionHasErrors(['name', 'location_id']);
    }

    #[Test]
    public function store_products_creates_sections_and_products(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create(['location_id' => $location->id, 'tenant_id' => 'test-tenant']);

        $this->tenant->onboarding_step = 3;
        $this->tenant->save();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.products'), [
                'menu_id' => $menu->id,
                'products' => [
                    ['name' => 'Ensalada César', 'price' => '8.50', 'section_name' => 'Entrantes'],
                    ['name' => 'Pizza Margarita', 'price' => '12.00', 'section_name' => 'Principales'],
                ],
            ]);

        $response->assertRedirect($this->tenantUrl('tenant.onboarding.show'));

        $this->assertDatabaseHas('sections', ['name' => 'Entrantes', 'menu_id' => $menu->id]);
        $this->assertDatabaseHas('sections', ['name' => 'Principales', 'menu_id' => $menu->id]);
        $this->assertDatabaseHas('products', ['name' => 'Ensalada César', 'tenant_id' => 'test-tenant']);
        $this->assertDatabaseHas('products', ['name' => 'Pizza Margarita', 'tenant_id' => 'test-tenant']);

        $this->tenant->refresh();
        $this->assertEquals(4, $this->tenant->onboarding_step);
    }

    #[Test]
    public function store_products_requires_at_least_one_product(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create(['location_id' => $location->id, 'tenant_id' => 'test-tenant']);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.products'), [
                'menu_id' => $menu->id,
                'products' => [],
            ]);

        $response->assertSessionHasErrors('products');
    }

    #[Test]
    public function complete_generates_qr_and_marks_onboarding_done(): void
    {
        Storage::fake('public');

        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create(['location_id' => $location->id, 'tenant_id' => 'test-tenant', 'is_active' => true]);

        $this->tenant->onboarding_step = 4;
        $this->tenant->save();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.complete'), [
                'menu_id' => $menu->id,
            ]);

        $response->assertRedirect($this->tenantUrl('tenant.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('qr_codes', ['menu_id' => $menu->id]);

        $this->tenant->refresh();
        $this->assertNotNull($this->tenant->onboarding_completed_at);
    }

    #[Test]
    public function complete_requires_valid_menu_id(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.complete'), [
                'menu_id' => 99999,
            ]);

        $response->assertSessionHasErrors('menu_id');
    }
}
