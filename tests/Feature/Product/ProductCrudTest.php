<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Allergen;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Menu $menu;

    protected Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

        $this->menu = Menu::factory()->create(['is_active' => true]);
        $this->section = Section::create([
            'name' => 'General',
            'menu_id' => $this->menu->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    protected function tenantUrl(string $routeName, array $params = []): string
    {
        return 'http://test.localhost'.route($routeName, $params, false);
    }

    protected function subscribePro(): void
    {
        $plan = Plan::factory()->create([
            'slug' => 'pro-test',
            'max_products' => 0, // unlimited
        ]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Store
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function store_requires_authentication(): void
    {
        $response = $this->post($this->tenantUrl('tenant.menus.products.store', ['menu' => $this->menu->id]), []);
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function store_creates_product_and_attaches_to_section_and_menu(): void
    {
        $allergen = Allergen::create([
            'name' => 'Gluten',
            'code' => 'gluten',
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.products.store', ['menu' => $this->menu->id]), [
                'name' => 'Tortilla española',
                'price' => '8.50',
                'section_id' => $this->section->id,
                'allergen_ids' => [$allergen->id],
                'ingredient_names' => ['huevo', 'patata'],
                'tags' => ['vegetarian'],
            ]);

        $response->assertRedirect();

        $product = Product::where('name', 'Tortilla española')->first();
        $this->assertNotNull($product);
        $this->assertEquals('8.50', $product->price);

        // Attached to section pivot
        $this->assertDatabaseHas('product_section', [
            'product_id' => $product->id,
            'section_id' => $this->section->id,
            'tenant_id' => $this->tenant->id,
        ]);

        // Attached to menu pivot
        $this->assertDatabaseHas('menu_product', [
            'product_id' => $product->id,
            'menu_id' => $this->menu->id,
        ]);

        // Allergen attached
        $this->assertDatabaseHas('allergen_product', [
            'allergen_id' => $allergen->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
        ]);

        // Ingredients created and attached
        $this->assertDatabaseHas('ingredients', ['name' => 'huevo', 'tenant_id' => $this->tenant->id]);
        $this->assertDatabaseHas('ingredients', ['name' => 'patata', 'tenant_id' => $this->tenant->id]);

        // Tags
        $this->assertEquals(['vegetarian'], $product->fresh()->tags);
    }

    #[Test]
    public function store_respects_plan_product_limit(): void
    {
        // Create a plan with max 1 product and subscribe
        $plan = Plan::factory()->create([
            'slug' => 'free-limit-test',
            'max_products' => 1,
        ]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);

        // Create the first product to hit the limit
        Product::create([
            'name' => 'Existing product',
            'price' => 5.00,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.products.store', ['menu' => $this->menu->id]), [
                'name' => 'New product',
                'price' => '3.00',
                'section_id' => $this->section->id,
            ]);

        $response->assertRedirect(); // back with error
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('products', ['name' => 'New product']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Update
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function update_changes_product_and_syncs_allergens(): void
    {
        $product = Product::create([
            'name' => 'Ensalada',
            'price' => 7.00,
            'tenant_id' => $this->tenant->id,
        ]);

        $allergen = Allergen::create([
            'name' => 'Pescado',
            'code' => 'fish',
            'tenant_id' => $this->tenant->id,
        ]);

        // Attach to section and menu
        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $this->section->id,
            'tenant_id' => $this->tenant->id,
        ]);
        DB::table('menu_product')->insert([
            'menu_id' => $this->menu->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.products.update', ['product' => $product->id]), [
                'name' => 'Ensalada actualizada',
                'price' => '9.00',
                'section_id' => $this->section->id,
                'allergen_ids' => [$allergen->id],
                'ingredient_names' => [],
                'tags' => [],
            ]);

        $response->assertRedirect();
        $this->assertEquals('Ensalada actualizada', $product->fresh()->name);
        $this->assertDatabaseHas('allergen_product', [
            'allergen_id' => $allergen->id,
            'product_id' => $product->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Destroy
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function destroy_deletes_product_and_pivots(): void
    {
        $product = Product::create([
            'name' => 'Producto a borrar',
            'price' => 5.00,
            'tenant_id' => $this->tenant->id,
        ]);

        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $this->section->id,
            'tenant_id' => $this->tenant->id,
        ]);
        DB::table('menu_product')->insert([
            'menu_id' => $this->menu->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.products.destroy', ['product' => $product->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_section', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('menu_product', ['product_id' => $product->id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Duplicate
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function duplicate_clones_product_with_pivots(): void
    {
        $product = Product::create([
            'name' => 'Original',
            'price' => 12.00,
            'tenant_id' => $this->tenant->id,
        ]);

        $allergen = Allergen::create([
            'name' => 'Soja',
            'code' => 'soy',
            'tenant_id' => $this->tenant->id,
        ]);

        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $this->section->id,
            'tenant_id' => $this->tenant->id,
        ]);
        DB::table('menu_product')->insert([
            'menu_id' => $this->menu->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);
        DB::table('allergen_product')->insert([
            'allergen_id' => $allergen->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.products.duplicate', ['product' => $product->id]));

        $response->assertRedirect();

        $clone = Product::where('name', 'Original (copia)')->first();
        $this->assertNotNull($clone);

        $this->assertDatabaseHas('product_section', [
            'product_id' => $clone->id,
            'section_id' => $this->section->id,
        ]);
        $this->assertDatabaseHas('allergen_product', [
            'allergen_id' => $allergen->id,
            'product_id' => $clone->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Tenant isolation
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function tenant_cannot_edit_product_of_another_tenant(): void
    {
        // End current tenancy
        tenancy()->end();

        // Create a second tenant
        Event::fake([TenantCreated::class, TenantDeleted::class]);
        $otherTenant = Tenant::create(['id' => 'other-tenant']);
        $otherTenant->domains()->create(['domain' => 'other.localhost']);
        tenancy()->initialize($otherTenant);
        $otherTenant->onboarding_completed_at = now();
        $otherTenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $otherUser = User::factory()->create();

        $otherMenu = Menu::factory()->create(['is_active' => true]);
        $otherSection = Section::create([
            'name' => 'Other section',
            'menu_id' => $otherMenu->id,
            'tenant_id' => $otherTenant->id,
            'sort_order' => 1,
        ]);
        $otherProduct = Product::create([
            'name' => 'Other product',
            'price' => 5.00,
            'tenant_id' => $otherTenant->id,
        ]);
        DB::table('product_section')->insert([
            'product_id' => $otherProduct->id,
            'section_id' => $otherSection->id,
            'tenant_id' => $otherTenant->id,
        ]);

        tenancy()->end();

        // Re-init as the first tenant and try to edit the other tenant's product
        tenancy()->initialize($this->tenant);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.products.update', ['product' => $otherProduct->id]), [
                'name' => 'Hacked',
                'price' => '1.00',
                'section_id' => $this->section->id,
                'allergen_ids' => [],
                'ingredient_names' => [],
                'tags' => [],
            ]);

        // BelongsToTenant scope: product not found → 404 or redirect
        $this->assertNotEquals('Hacked', $otherProduct->fresh()->name);
    }
}
