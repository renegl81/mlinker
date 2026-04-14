<?php

namespace Tests\Feature\Tenant;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenantA = Tenant::create(['id' => 'tenant-a']);
        $this->tenantA->domains()->create(['domain' => 'tenant-a.localhost']);

        $this->tenantB = Tenant::create(['id' => 'tenant-b']);
        $this->tenantB->domains()->create(['domain' => 'tenant-b.localhost']);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Location isolation
    // -------------------------------------------------------------------------

    #[Test]
    public function tenant_a_locations_are_not_visible_from_tenant_b(): void
    {
        tenancy()->initialize($this->tenantA);
        Location::factory()->count(3)->create(['tenant_id' => 'tenant-a']);
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        Location::factory()->count(2)->create(['tenant_id' => 'tenant-b']);

        $locations = Location::all();

        $this->assertCount(2, $locations);
        $this->assertTrue($locations->every(fn ($l) => $l->tenant_id === 'tenant-b'));
    }

    #[Test]
    public function tenant_b_locations_are_not_visible_from_tenant_a(): void
    {
        tenancy()->initialize($this->tenantB);
        Location::factory()->count(5)->create(['tenant_id' => 'tenant-b']);
        tenancy()->end();

        tenancy()->initialize($this->tenantA);
        Location::factory()->create(['tenant_id' => 'tenant-a']);

        $locations = Location::all();

        $this->assertCount(1, $locations);
        $this->assertEquals('tenant-a', $locations->first()->tenant_id);
    }

    // -------------------------------------------------------------------------
    // Menu isolation
    // -------------------------------------------------------------------------

    #[Test]
    public function tenant_a_menus_are_not_visible_from_tenant_b(): void
    {
        tenancy()->initialize($this->tenantA);
        $locationA = Location::factory()->create(['tenant_id' => 'tenant-a']);
        Menu::factory()->count(3)->create([
            'location_id' => $locationA->id,
            'tenant_id' => 'tenant-a',
        ]);
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        $locationB = Location::factory()->create(['tenant_id' => 'tenant-b']);
        Menu::factory()->count(2)->create([
            'location_id' => $locationB->id,
            'tenant_id' => 'tenant-b',
        ]);

        $menus = Menu::all();

        $this->assertCount(2, $menus);
        $this->assertTrue($menus->every(fn ($m) => $m->tenant_id === 'tenant-b'));
    }

    // -------------------------------------------------------------------------
    // Section isolation
    // -------------------------------------------------------------------------

    #[Test]
    public function sections_are_isolated_per_tenant(): void
    {
        tenancy()->initialize($this->tenantA);
        $locationA = Location::factory()->create(['tenant_id' => 'tenant-a']);
        $menuA = Menu::factory()->create(['location_id' => $locationA->id, 'tenant_id' => 'tenant-a']);
        Section::factory()->count(4)->create(['menu_id' => $menuA->id, 'tenant_id' => 'tenant-a']);
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        $locationB = Location::factory()->create(['tenant_id' => 'tenant-b']);
        $menuB = Menu::factory()->create(['location_id' => $locationB->id, 'tenant_id' => 'tenant-b']);
        Section::factory()->count(2)->create(['menu_id' => $menuB->id, 'tenant_id' => 'tenant-b']);

        $sections = Section::all();

        $this->assertCount(2, $sections);
        $this->assertTrue($sections->every(fn ($s) => $s->tenant_id === 'tenant-b'));
    }

    // -------------------------------------------------------------------------
    // Product isolation
    // -------------------------------------------------------------------------

    #[Test]
    public function products_are_isolated_per_tenant(): void
    {
        tenancy()->initialize($this->tenantA);
        Product::factory()->count(10)->create(['tenant_id' => 'tenant-a']);
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        Product::factory()->count(3)->create(['tenant_id' => 'tenant-b']);

        $products = Product::all();

        $this->assertCount(3, $products);
        $this->assertTrue($products->every(fn ($p) => $p->tenant_id === 'tenant-b'));
    }

    // -------------------------------------------------------------------------
    // API isolation: tenant B token cannot see tenant A resources
    // -------------------------------------------------------------------------

    #[Test]
    public function tenant_b_api_token_cannot_list_tenant_a_menus(): void
    {
        // Setup tenant A: location + menus
        tenancy()->initialize($this->tenantA);
        $planA = Plan::factory()->create(['has_api_access' => true, 'slug' => 'api-a', 'name' => 'API Plan A']);
        Subscription::create([
            'tenant_id' => 'tenant-a',
            'plan_id' => $planA->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);
        $locationA = Location::factory()->create(['tenant_id' => 'tenant-a']);
        Menu::factory()->count(3)->create([
            'location_id' => $locationA->id,
            'tenant_id' => 'tenant-a',
        ]);
        tenancy()->end();

        // Setup tenant B: token with api access
        tenancy()->initialize($this->tenantB);
        $planB = Plan::factory()->create(['has_api_access' => true, 'slug' => 'api-b', 'name' => 'API Plan B']);
        Subscription::create([
            'tenant_id' => 'tenant-b',
            'plan_id' => $planB->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);
        $tokenB = $this->tenantB->createToken('test')->plainTextToken;
        tenancy()->end();

        // Tenant B makes request — should only see its own menus (0 in this case)
        tenancy()->initialize($this->tenantB);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenB)
            ->getJson('/api/v1/menus');

        $response->assertStatus(200);
        // Tenant B has no menus, so it gets 0 — not 3 from tenant A
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function tenant_a_api_token_cannot_see_tenant_b_locations(): void
    {
        // Create locations for tenant B
        tenancy()->initialize($this->tenantB);
        Location::factory()->count(5)->create(['tenant_id' => 'tenant-b']);
        tenancy()->end();

        // Tenant A gets an API token with access
        tenancy()->initialize($this->tenantA);
        $planA = Plan::factory()->create(['has_api_access' => true, 'slug' => 'api-loc-a', 'name' => 'API Loc A']);
        Subscription::create([
            'tenant_id' => 'tenant-a',
            'plan_id' => $planA->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);
        // Create 2 locations for tenant A
        Location::factory()->count(2)->create(['tenant_id' => 'tenant-a']);
        $tokenA = $this->tenantA->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson('/api/v1/locations');

        $response->assertStatus(200);
        // Tenant A sees only its 2 locations, not the 5 from tenant B
        $this->assertCount(2, $response->json('data'));
    }

    // -------------------------------------------------------------------------
    // Cross-tenant access: HTTP admin routes
    // -------------------------------------------------------------------------

    #[Test]
    public function tenant_a_user_cannot_access_tenant_b_admin_dashboard_via_domain(): void
    {
        Role::query()->firstOrCreate(['name' => 'Owner']);

        tenancy()->initialize($this->tenantA);
        $userA = User::factory()->create();
        tenancy()->end();

        // User of tenant A tries to hit tenant B's domain
        $response = $this->actingAs($userA)
            ->get('http://tenant-b.localhost'.route('tenant.dashboard', [], false));

        // Should either redirect (unauthenticated in B's context) or return 404/403
        $this->assertNotEquals(200, $response->status());
    }

    // -------------------------------------------------------------------------
    // Tenant isolation with BelongsToTenant scope: direct query check
    // -------------------------------------------------------------------------

    #[Test]
    public function belongs_to_tenant_scope_filters_correctly_when_switching_contexts(): void
    {
        tenancy()->initialize($this->tenantA);
        Location::factory()->create(['name' => 'Location A1', 'tenant_id' => 'tenant-a']);
        Location::factory()->create(['name' => 'Location A2', 'tenant_id' => 'tenant-a']);
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        Location::factory()->create(['name' => 'Location B1', 'tenant_id' => 'tenant-b']);

        // Within tenant B's context, only B1 should be visible
        $names = Location::pluck('name')->toArray();
        $this->assertContains('Location B1', $names);
        $this->assertNotContains('Location A1', $names);
        $this->assertNotContains('Location A2', $names);
    }
}
