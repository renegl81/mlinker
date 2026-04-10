<?php

namespace Tests\Feature\Api\V1;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class ApiMenuTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'api-test-tenant']);
        $this->tenant->domains()->create(['domain' => 'api-test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->token = $this->tenant->createToken('test-token')->plainTextToken;
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }

    // ---------------------------------------------------------------
    // Authentication
    // ---------------------------------------------------------------

    #[Test]
    public function unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/menus');

        $response->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // Plan access control
    // ---------------------------------------------------------------

    #[Test]
    public function tenant_without_api_access_plan_returns_403(): void
    {
        $plan = Plan::factory()->create(['has_api_access' => false]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/menus');

        $response->assertStatus(403)
            ->assertJson(['error' => 'API access requires Business or Enterprise plan']);
    }

    #[Test]
    public function tenant_with_api_access_plan_can_list_menus(): void
    {
        $plan = Plan::factory()->create(['has_api_access' => true]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        Menu::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/menus');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_active'],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    // ---------------------------------------------------------------
    // Menu show
    // ---------------------------------------------------------------

    #[Test]
    public function authenticated_tenant_can_get_menu_with_relations(): void
    {
        $plan = Plan::factory()->create(['has_api_access' => true]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        $location = Location::factory()->create();
        $menu = Menu::factory()->create(['location_id' => $location->id, 'is_active' => true]);
        Section::factory()->create(['menu_id' => $menu->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/menus/{$menu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'is_active',
                    'sections',
                ],
            ]);
    }

    // ---------------------------------------------------------------
    // QR Code endpoint
    // ---------------------------------------------------------------

    #[Test]
    public function authenticated_tenant_can_get_menu_qr_code(): void
    {
        $plan = Plan::factory()->create(['has_api_access' => true]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        $menu = Menu::factory()->create(['is_active' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/menus/{$menu->id}/qr-code");

        $response->assertStatus(200)
            ->assertJsonStructure(['url', 'image_url']);
    }

    // ---------------------------------------------------------------
    // Locations
    // ---------------------------------------------------------------

    #[Test]
    public function authenticated_tenant_can_list_locations(): void
    {
        $plan = Plan::factory()->create(['has_api_access' => true]);
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        Location::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/locations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    // ---------------------------------------------------------------
    // Public endpoint
    // ---------------------------------------------------------------

    #[Test]
    public function public_menu_endpoint_returns_active_menu_without_auth(): void
    {
        $location = Location::factory()->create();
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);
        Section::factory()->create(['menu_id' => $menu->id]);

        $response = $this->getJson("/api/public/menus/{$menu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'is_active', 'sections'],
            ]);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=300', $cacheControl);
        $this->assertNotNull($response->headers->get('ETag'));
    }

    #[Test]
    public function public_menu_endpoint_returns_404_for_inactive_menu(): void
    {
        $menu = Menu::factory()->create(['is_active' => false]);

        $response = $this->getJson("/api/public/menus/{$menu->id}");

        $response->assertStatus(404);
    }

    #[Test]
    public function public_menu_endpoint_returns_404_for_nonexistent_menu(): void
    {
        $response = $this->getJson('/api/public/menus/99999');

        $response->assertStatus(404);
    }
}
