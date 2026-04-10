<?php

namespace Tests\Feature\Tenant\Catalog;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class CatalogProductTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('plan:free');
        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner', 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function tenantUrl(string $routeName, array $params = []): string
    {
        return 'http://test.localhost'.route($routeName, $params, false);
    }

    private function subscribeTo(array $overrides = []): Plan
    {
        $plan = Plan::factory()->create(array_merge([
            'slug' => 'plan-'.uniqid(),
            'has_catalog' => false,
            'show_branding' => false,
            'max_locations' => 5,
            'max_menus_per_location' => 10,
            'max_products' => 500,
            'max_images' => 0,
        ], $overrides));

        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        return $plan;
    }

    #[Test]
    public function index_requires_catalog_plan(): void
    {
        $this->subscribeTo(['has_catalog' => false]);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.catalog.products.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function index_lists_products_for_business_plan(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        Product::factory()->create(['name' => 'Paella']);
        Product::factory()->create(['name' => 'Tortilla']);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.catalog.products.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/tenant/catalog/Products')
            ->has('products.data', 2)
        );
    }

    #[Test]
    public function index_filters_by_search_query(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        Product::factory()->create(['name' => 'Paella de mariscos']);
        Product::factory()->create(['name' => 'Tortilla española']);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.catalog.products.index').'?q=paella');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Paella de mariscos')
        );
    }

    #[Test]
    public function bulk_delete_removes_selected_products(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        $a = Product::factory()->create(['name' => 'A']);
        $b = Product::factory()->create(['name' => 'B']);
        $c = Product::factory()->create(['name' => 'C']);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.catalog.products.bulk-delete'), [
                'product_ids' => [$a->id, $b->id],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $a->id]);
        $this->assertDatabaseMissing('products', ['id' => $b->id]);
        $this->assertDatabaseHas('products', ['id' => $c->id]);
    }

    #[Test]
    public function bulk_attach_menu_creates_pivots_with_sort_order(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create(['location_id' => $location->id]);
        $section = Section::factory()->create(['menu_id' => $menu->id]);

        $a = Product::factory()->create();
        $b = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.catalog.products.bulk-attach-menu'), [
                'product_ids' => [$a->id, $b->id],
                'menu_id' => $menu->id,
                'section_id' => $section->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_product', ['menu_id' => $menu->id, 'product_id' => $a->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('menu_product', ['menu_id' => $menu->id, 'product_id' => $b->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('product_section', ['section_id' => $section->id, 'product_id' => $a->id]);
        $this->assertDatabaseHas('product_section', ['section_id' => $section->id, 'product_id' => $b->id]);
    }

    #[Test]
    public function free_plan_cannot_bulk_delete(): void
    {
        $this->subscribeTo(['has_catalog' => false]);

        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.catalog.products.bulk-delete'), [
                'product_ids' => [$product->id],
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
