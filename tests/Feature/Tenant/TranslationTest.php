<?php

namespace Tests\Feature\Tenant;

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

class TranslationTest extends TestCase
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

        // Mark onboarding as completed so we don't get redirected
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

    private function subscribeTo(array $planOverrides = []): Plan
    {
        $plan = Plan::factory()->create(array_merge([
            'slug' => 'plan-'.uniqid(),
            'has_multilang' => false,
            'show_branding' => false,
            'max_locations' => 5,
            'max_menus_per_location' => 10,
            'max_products' => 100,
            'max_images' => 0,
        ], $planOverrides));

        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        return $plan;
    }

    // ─── Public menu with ?lang=en ───────────────────────────────────────────

    #[Test]
    public function menu_returns_translated_fields_when_lang_en_and_plan_has_multilang(): void
    {
        $this->subscribeTo(['has_multilang' => true]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
            'name' => 'Menú de Verano',
            'translations' => ['en' => ['name' => 'Summer Menu', 'description' => 'A summer menu']],
        ]);

        $section = Section::factory()->create([
            'menu_id' => $menu->id,
            'name' => 'Entrantes',
            'translations' => ['en' => ['name' => 'Starters', 'description' => 'Fresh starters']],
        ]);

        $product = Product::factory()->create([
            'name' => 'Ensalada',
            'translations' => ['en' => ['name' => 'Salad', 'description' => 'Fresh salad']],
        ]);

        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $section->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->get(
            route('tenant_public.tenant_menu_show', ['menu' => $menu->id]).'?lang=en'
        );

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('menu.name', 'Summer Menu')
            ->where('menu.sections.0.name', 'Starters')
            ->where('menu.sections.0.products.0.name', 'Salad')
            ->where('locale', 'en')
        );
    }

    #[Test]
    public function menu_returns_original_fields_when_no_translations_exist(): void
    {
        $this->subscribeTo(['has_multilang' => true]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
            'name' => 'Menú de Verano',
            'translations' => null,
        ]);

        $response = $this->get(
            route('tenant_public.tenant_menu_show', ['menu' => $menu->id]).'?lang=en'
        );

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('menu.name', 'Menú de Verano')
        );
    }

    #[Test]
    public function menu_ignores_lang_param_when_plan_has_no_multilang(): void
    {
        $this->subscribeTo(['has_multilang' => false]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
            'name' => 'Menú Original',
            'translations' => ['en' => ['name' => 'Should Not Appear', 'description' => '']],
        ]);

        $response = $this->get(
            route('tenant_public.tenant_menu_show', ['menu' => $menu->id]).'?lang=en'
        );

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('menu.name', 'Menú Original')
        );
    }

    #[Test]
    public function menu_defaults_to_es_locale_without_lang_param(): void
    {
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
            'name' => 'Carta Española',
        ]);

        $response = $this->get(route('tenant_public.tenant_menu_show', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('locale', 'es')
            ->where('menu.name', 'Carta Española')
        );
    }

    // ─── Translation endpoint (PUT /panel/menus/{menu}/translations) ─────────

    #[Test]
    public function saving_translations_requires_multilang_plan(): void
    {
        $this->subscribeTo(['has_multilang' => false]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create(['location_id' => $location->id, 'is_active' => true]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.menus.translations.update', ['menu' => $menu->id]), [
                'sections' => [],
                'products' => [],
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function saving_translations_works_for_business_plan(): void
    {
        $this->subscribeTo(['has_multilang' => true]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create(['location_id' => $location->id, 'is_active' => true]);
        $section = Section::factory()->create(['menu_id' => $menu->id, 'name' => 'Entrantes']);
        $product = Product::factory()->create(['name' => 'Ensalada']);
        DB::table('product_section')->insert([
            'product_id' => $product->id,
            'section_id' => $section->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.menus.translations.update', ['menu' => $menu->id]), [
                'sections' => [
                    ['id' => $section->id, 'translations' => ['en' => ['name' => 'Starters', 'description' => '']]],
                ],
                'products' => [
                    ['id' => $product->id, 'translations' => ['en' => ['name' => 'Salad', 'description' => 'Fresh']]],
                ],
            ]);

        $response->assertRedirect();

        $section->refresh();
        $this->assertEquals('Starters', $section->translations['en']['name']);

        $product->refresh();
        $this->assertEquals('Salad', $product->translations['en']['name']);
    }

    #[Test]
    public function free_plan_tenant_gets_403_on_translation_endpoint(): void
    {
        // No subscription → falls back to free plan (has_multilang = false)
        $location = Location::factory()->create();
        $menu = Menu::factory()->create(['location_id' => $location->id, 'is_active' => true]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.menus.translations.update', ['menu' => $menu->id]), [
                'sections' => [],
                'products' => [],
            ]);

        $response->assertStatus(403);
    }
}
