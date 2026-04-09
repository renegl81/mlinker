<?php

namespace Tests\Feature\Tenant;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent DB creation/deletion jobs from firing in single-database setup
        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }

    #[Test]
    public function active_menu_renders_with_sections_and_products(): void
    {
        $template = Template::factory()->create(['component_name' => 'Basic']);
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'template_id' => $template->id,
            'is_active' => true,
        ]);

        $section = Section::factory()->create(['menu_id' => $menu->id]);
        $product = Product::factory()->create();

        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $section->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('tenant/templates/Basic')
            ->has('menu')
            ->has('menu.sections', 1)
            ->has('menu.sections.0.products', 1)
        );
    }

    #[Test]
    public function inactive_menu_returns_404(): void
    {
        $menu = Menu::factory()->create(['is_active' => false]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(404);
    }

    #[Test]
    public function menu_loads_allergens_and_ingredients(): void
    {
        $template = Template::factory()->create(['component_name' => 'Basic']);
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'template_id' => $template->id,
            'is_active' => true,
        ]);

        $section = Section::factory()->create(['menu_id' => $menu->id]);
        $product = Product::factory()->create();
        $allergen = Allergen::factory()->create();
        $ingredient = Ingredient::factory()->create();

        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $section->id,
            'tenant_id' => $this->tenant->id,
        ]);

        DB::table('allergen_product')->insert([
            'allergen_id' => $allergen->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
        ]);

        DB::table('ingredient_product')->insert([
            'ingredient_id' => $ingredient->id,
            'product_id' => $product->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('menu.sections.0.products.0.allergens', 1)
            ->has('menu.sections.0.products.0.ingredients', 1)
        );
    }

    #[Test]
    public function public_menu_shows_branding_for_free_plan(): void
    {
        $plan = Plan::factory()->create(['show_branding' => true]);
        // Crear suscripción directamente con el tenant_id ya existente (Tenant no usa HasFactory)
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('showBranding', true)
        );
    }

    #[Test]
    public function public_menu_hides_branding_for_pro_plan(): void
    {
        $plan = Plan::factory()->create(['show_branding' => false]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('showBranding', false)
        );
    }

    #[Test]
    public function public_menu_shows_branding_true_when_no_subscription(): void
    {
        // Sin suscripción, el branding debe mostrarse por defecto
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('showBranding', true)
        );
    }

    #[Test]
    public function public_menu_includes_seo_meta(): void
    {
        $location = Location::factory()->create(['name' => 'Café Central']);
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'name' => 'Carta de Verano',
            'is_active' => true,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('meta')
            ->has('meta.title')
            ->has('meta.description')
            ->has('meta.url')
            ->where('meta.title', 'Carta de Verano — Café Central')
        );
    }

    #[Test]
    public function public_menu_includes_json_ld(): void
    {
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('jsonLd')
            ->where('jsonLd.@type', 'Restaurant')
            ->where('jsonLd.@context', 'https://schema.org')
        );
    }

    #[Test]
    public function short_url_renders_menu(): void
    {
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_short', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('menu')
            ->where('menu.id', $menu->id)
        );
    }
}
