<?php

namespace Tests\Feature\Actions;

use App\Actions\Menu\CreateMenu;
use App\Actions\Menu\DeleteMenu;
use App\Actions\Menu\UpdateMenu;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class MenuActionsTest extends TestCase
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

        $this->tenant = Tenant::create(['id' => 'menu-action-tenant']);
        $this->tenant->domains()->create(['domain' => 'menu-action.localhost']);

        tenancy()->initialize($this->tenant);

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->location = Location::factory()->create(['tenant_id' => 'menu-action-tenant']);
        $this->template = Template::factory()->create();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Carta de Verano',
            'description' => 'Menú especial de verano',
            'location_id' => $this->location->id,
            'template_id' => $this->template->id,
            'is_active' => true,
            'show_prices' => true,
            'show_currency' => false,
            'show_calories' => false,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // CreateMenu
    // -------------------------------------------------------------------------

    #[Test]
    public function create_menu_stores_record_with_correct_tenant_id(): void
    {
        $menu = (new CreateMenu)->execute($this->validData());

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertDatabaseHas('menus', [
            'name' => 'Carta de Verano',
            'tenant_id' => 'menu-action-tenant',
        ]);
    }

    #[Test]
    public function create_menu_links_to_provided_location(): void
    {
        $menu = (new CreateMenu)->execute($this->validData());

        $this->assertEquals($this->location->id, $menu->location_id);
    }

    #[Test]
    public function create_menu_defaults_is_active_to_true(): void
    {
        $data = $this->validData();
        unset($data['is_active']);

        $menu = (new CreateMenu)->execute($data);

        $this->assertTrue($menu->is_active);
    }

    #[Test]
    public function create_menu_stores_show_prices_flag(): void
    {
        $menu = (new CreateMenu)->execute($this->validData(['show_prices' => false]));

        $this->assertFalse($menu->show_prices);
    }

    // -------------------------------------------------------------------------
    // UpdateMenu
    // -------------------------------------------------------------------------

    #[Test]
    public function update_menu_modifies_name_and_description(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-action-tenant',
            'location_id' => $this->location->id,
        ]);

        $updated = (new UpdateMenu)->execute($menu, [
            'name' => 'Carta de Invierno',
            'description' => 'Menú de invierno',
            'template_id' => $this->template->id,
            'is_active' => true,
            'show_prices' => true,
            'show_currency' => false,
            'show_calories' => false,
        ]);

        $this->assertEquals('Carta de Invierno', $updated->name);
        $this->assertEquals('Menú de invierno', $updated->description);
    }

    #[Test]
    public function update_menu_can_deactivate_menu(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-action-tenant',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $updated = (new UpdateMenu)->execute($menu, [
            'name' => $menu->name,
            'description' => $menu->description,
            'template_id' => $this->template->id,
            'is_active' => false,
            'show_prices' => true,
            'show_currency' => false,
            'show_calories' => false,
        ]);

        $this->assertFalse($updated->is_active);
    }

    #[Test]
    public function update_menu_returns_fresh_model(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-action-tenant',
            'location_id' => $this->location->id,
        ]);

        $result = (new UpdateMenu)->execute($menu, [
            'name' => 'Nuevo Nombre',
            'description' => null,
            'template_id' => $this->template->id,
            'is_active' => true,
            'show_prices' => true,
            'show_currency' => false,
            'show_calories' => false,
        ]);

        $this->assertInstanceOf(Menu::class, $result);
        $this->assertEquals('Nuevo Nombre', $result->name);
    }

    // -------------------------------------------------------------------------
    // DeleteMenu
    // -------------------------------------------------------------------------

    #[Test]
    public function delete_menu_removes_record_from_database(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-action-tenant',
            'location_id' => $this->location->id,
        ]);
        $menuId = $menu->id;

        $result = (new DeleteMenu)->execute($menu);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('menus', ['id' => $menuId]);
    }

    #[Test]
    public function delete_menu_returns_true_on_success(): void
    {
        $menu = Menu::factory()->create([
            'tenant_id' => 'menu-action-tenant',
            'location_id' => $this->location->id,
        ]);

        $result = (new DeleteMenu)->execute($menu);

        $this->assertTrue($result);
    }
}
