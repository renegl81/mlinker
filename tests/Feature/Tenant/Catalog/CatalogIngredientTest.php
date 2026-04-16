<?php

namespace Tests\Feature\Tenant\Catalog;

use App\Models\Ingredient;
use App\Models\Plan;
use App\Models\Product;
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

class CatalogIngredientTest extends TestCase
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
            ->get($this->tenantUrl('tenant.catalog.ingredients.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function index_lists_ingredients_with_usage_count(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        $ingredient = Ingredient::create(['name' => 'tomate']);
        $product = Product::factory()->create();
        DB::table('ingredient_product')->insert([
            'ingredient_id' => $ingredient->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.catalog.ingredients.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/tenant/catalog/Ingredients')
            ->has('ingredients.data', 1)
            ->where('ingredients.data.0.products_count', 1)
        );
    }

    #[Test]
    public function update_name_validates_uniqueness_per_tenant(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        Ingredient::create(['name' => 'tomate']);
        $b = Ingredient::create(['name' => 'cebolla']);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.catalog.ingredients.update', ['ingredient' => $b->id]), [
                'name' => 'tomate',
            ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function update_translations_persists(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        $ingredient = Ingredient::create(['name' => 'tomate']);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.catalog.ingredients.translations', ['ingredient' => $ingredient->id]), [
                'translations' => [
                    'en' => ['name' => 'tomato'],
                    'fr' => ['name' => 'tomate'],
                ],
            ]);

        $response->assertRedirect();
        $ingredient->refresh();
        $this->assertEquals('tomato', $ingredient->translations['en']['name']);
        $this->assertEquals('tomate', $ingredient->translations['fr']['name']);
    }

    #[Test]
    public function merge_fuses_ingredients_and_reassigns_pivots(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        $survivor = Ingredient::create(['name' => 'Tomate', 'translations' => ['en' => ['name' => 'Tomato']]]);
        $absorbed1 = Ingredient::create(['name' => 'tomate', 'translations' => ['fr' => ['name' => 'tomate']]]);
        $absorbed2 = Ingredient::create(['name' => 'TOMATE']);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        DB::table('ingredient_product')->insert([
            ['ingredient_id' => $absorbed1->id, 'product_id' => $product1->id, 'tenant_id' => $this->tenant->id],
            ['ingredient_id' => $absorbed2->id, 'product_id' => $product2->id, 'tenant_id' => $this->tenant->id],
        ]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.catalog.ingredients.merge'), [
                'ingredient_ids' => [$survivor->id, $absorbed1->id, $absorbed2->id],
                'survivor_id' => $survivor->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ingredients', ['id' => $survivor->id]);
        $this->assertDatabaseMissing('ingredients', ['id' => $absorbed1->id]);
        $this->assertDatabaseMissing('ingredients', ['id' => $absorbed2->id]);

        // Pivots reassigned to survivor
        $this->assertDatabaseHas('ingredient_product', ['ingredient_id' => $survivor->id, 'product_id' => $product1->id]);
        $this->assertDatabaseHas('ingredient_product', ['ingredient_id' => $survivor->id, 'product_id' => $product2->id]);

        // Translations deep-merged (survivor keeps EN, absorbs FR)
        $survivor->refresh();
        $this->assertEquals('Tomato', $survivor->translations['en']['name']);
        $this->assertEquals('tomate', $survivor->translations['fr']['name']);
    }

    #[Test]
    public function destroy_removes_ingredient_and_pivots(): void
    {
        $this->subscribeTo(['has_catalog' => true]);

        $ingredient = Ingredient::create(['name' => 'cebolla']);
        $product = Product::factory()->create();
        DB::table('ingredient_product')->insert([
            'ingredient_id' => $ingredient->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.catalog.ingredients.destroy', ['ingredient' => $ingredient->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('ingredients', ['id' => $ingredient->id]);
        $this->assertDatabaseMissing('ingredient_product', ['ingredient_id' => $ingredient->id]);
    }
}
