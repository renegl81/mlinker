<?php

declare(strict_types=1);

namespace Tests\Feature\Analytics;

use App\Actions\Analytics\GetTenantOverview;
use App\Jobs\TrackMenuView;
use App\Models\Menu;
use App\Models\MenuView;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Menu $menu;

    protected Plan $freePlan;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('plan:free');

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-analytics-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

        $this->freePlan = Plan::factory()->create([
            'slug' => 'free',
            'name' => 'Free',
            'price' => '0.00',
            'stripe_price_id' => null,
            'trial_days' => 0,
            'is_active' => true,
            'sort_order' => 0,
            'has_analytics' => false,
        ]);

        $this->menu = Menu::factory()->create([
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function tenantUrl(string $path): string
    {
        return 'http://test.localhost'.$path;
    }

    private function subscribeFree(): Subscription
    {
        return Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->freePlan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);
    }

    private function subscribePro(): Subscription
    {
        $pro = Plan::factory()->create([
            'slug' => 'pro',
            'name' => 'Pro',
            'price' => '14.99',
            'stripe_price_id' => 'price_pro',
            'is_active' => true,
            'has_analytics' => true,
            'sort_order' => 1,
        ]);

        return Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $pro->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // TrackMenuView Job
    // -------------------------------------------------------------------------

    public function test_visiting_a_public_menu_creates_a_menu_view_record(): void
    {
        config(['queue.default' => 'sync']);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
            ->get($this->tenantUrl("/menu/{$this->menu->id}"));

        $response->assertOk();

        $this->assertDatabaseHas('menu_views', [
            'menu_id' => $this->menu->id,
            'tenant_id' => $this->tenant->id,
            'ip' => '1.2.3.4',
        ]);
    }

    public function test_same_ip_and_menu_within_30_minutes_does_not_create_duplicate(): void
    {
        config(['queue.default' => 'sync']);

        TrackMenuView::dispatchSync(
            $this->menu->id,
            $this->tenant->id,
            '1.2.3.4',
            'TestAgent',
            null,
        );

        TrackMenuView::dispatchSync(
            $this->menu->id,
            $this->tenant->id,
            '1.2.3.4',
            'TestAgent',
            null,
        );

        $this->assertSame(
            1,
            MenuView::withoutGlobalScopes()
                ->where('menu_id', $this->menu->id)
                ->where('ip', '1.2.3.4')
                ->count(),
        );
    }

    public function test_different_ips_create_separate_menu_view_records(): void
    {
        config(['queue.default' => 'sync']);

        TrackMenuView::dispatchSync($this->menu->id, $this->tenant->id, '1.1.1.1', null, null);
        TrackMenuView::dispatchSync($this->menu->id, $this->tenant->id, '2.2.2.2', null, null);

        $this->assertSame(
            2,
            MenuView::withoutGlobalScopes()
                ->where('menu_id', $this->menu->id)
                ->count(),
        );
    }

    public function test_same_ip_after_30_minutes_creates_a_new_record(): void
    {
        config(['queue.default' => 'sync']);

        // Primera vista hace 31 minutos
        MenuView::withoutGlobalScopes()->create([
            'menu_id' => $this->menu->id,
            'tenant_id' => $this->tenant->id,
            'ip' => '5.5.5.5',
            'user_agent' => null,
            'referer' => null,
            'viewed_at' => Carbon::now()->subMinutes(31),
        ]);

        // Segunda vista ahora
        TrackMenuView::dispatchSync($this->menu->id, $this->tenant->id, '5.5.5.5', null, null);

        $this->assertSame(
            2,
            MenuView::withoutGlobalScopes()
                ->where('menu_id', $this->menu->id)
                ->where('ip', '5.5.5.5')
                ->count(),
        );
    }

    // -------------------------------------------------------------------------
    // GetTenantOverview Action
    // -------------------------------------------------------------------------

    public function test_get_tenant_overview_returns_correct_structure(): void
    {
        // Crear algunas vistas
        for ($i = 0; $i < 5; $i++) {
            MenuView::withoutGlobalScopes()->create([
                'menu_id' => $this->menu->id,
                'tenant_id' => $this->tenant->id,
                'ip' => "10.0.0.{$i}",
                'user_agent' => null,
                'referer' => $i % 2 === 0 ? null : 'https://google.com/search',
                'viewed_at' => Carbon::now()->subDays($i),
            ]);
        }

        $action = new GetTenantOverview;
        $result = $action->execute($this->tenant->id, 30);

        $this->assertArrayHasKey('total_views', $result);
        $this->assertArrayHasKey('views_by_day', $result);
        $this->assertArrayHasKey('top_menus', $result);
        $this->assertArrayHasKey('views_by_source', $result);
        $this->assertArrayHasKey('current_period', $result);

        $this->assertSame(5, $result['total_views']);
        $this->assertCount(31, $result['views_by_day']); // 0..30 días inclusive

        $this->assertArrayHasKey('date', $result['views_by_day'][0]);
        $this->assertArrayHasKey('count', $result['views_by_day'][0]);

        $this->assertNotEmpty($result['top_menus']);
        $this->assertArrayHasKey('menu_id', $result['top_menus'][0]);
        $this->assertArrayHasKey('name', $result['top_menus'][0]);
        $this->assertArrayHasKey('count', $result['top_menus'][0]);

        $this->assertArrayHasKey('QR', $result['views_by_source']);
        $this->assertArrayHasKey('Google', $result['views_by_source']);

        // 3 vistas con referer null => QR, 2 con google => Google
        $this->assertSame(3, $result['views_by_source']['QR']);
        $this->assertSame(2, $result['views_by_source']['Google']);

        $this->assertArrayHasKey('start', $result['current_period']);
        $this->assertArrayHasKey('end', $result['current_period']);
        $this->assertSame(30, $result['current_period']['days']);
    }

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    public function test_dashboard_renders_for_pro_tenant_with_analytics(): void
    {
        $this->subscribePro();

        $this->actingAs($this->user)
            ->get($this->tenantUrl('/panel'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/tenant/Dashboard')
                ->where('hasAnalytics', true)
                ->has('total_views')
                ->has('views_by_day')
                ->has('top_menus')
                ->has('views_by_source')
                ->has('current_period')
            );
    }

    public function test_dashboard_renders_for_free_tenant_without_analytics(): void
    {
        $this->subscribeFree();

        $this->actingAs($this->user)
            ->get($this->tenantUrl('/panel'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/tenant/Dashboard')
                ->where('hasAnalytics', false)
                ->has('total_views')
            );
    }
}
