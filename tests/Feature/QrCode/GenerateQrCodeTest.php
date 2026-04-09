<?php

namespace Tests\Feature\QrCode;

use App\Actions\QrCode\GenerateQrCode;
use App\Models\Menu;
use App\Models\QRCode;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class GenerateQrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent DB creation/deletion jobs from firing in single-database setup
        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }

    #[Test]
    public function action_creates_qr_code_with_valid_image(): void
    {
        Storage::fake('public');

        $menu = Menu::factory()->create(['is_active' => true]);

        $action = new GenerateQrCode;
        $qr = $action->execute($menu);

        $this->assertInstanceOf(QRCode::class, $qr);
        $this->assertEquals($menu->id, $qr->menu_id);
        $this->assertNotNull($qr->image_url);
        $this->assertNotNull($qr->url);

        Storage::disk('public')->assertExists($qr->image_url);
    }

    #[Test]
    public function action_overwrites_existing_qr_code(): void
    {
        Storage::fake('public');

        $menu = Menu::factory()->create(['is_active' => true]);

        $action = new GenerateQrCode;
        $first = $action->execute($menu);
        $second = $action->execute($menu);

        $this->assertEquals($first->id, $second->id);
        $this->assertCount(1, QRCode::where('menu_id', $menu->id)->get());

        Storage::disk('public')->assertExists($second->image_url);
    }

    /**
     * Build a tenant-scoped URL for testing (resolves the route on the tenant domain).
     */
    protected function tenantUrl(string $routeName, array $params = []): string
    {
        $path = route($routeName, $params, false);

        return 'http://test.localhost'.$path;
    }

    #[Test]
    public function generate_endpoint_requires_authentication(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->post($this->tenantUrl('tenant.menus.qr-code.generate', ['menu' => $menu->id]));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function generate_endpoint_creates_qr_for_authenticated_user(): void
    {
        Storage::fake('public');

        $menu = Menu::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.qr-code.generate', ['menu' => $menu->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('qr_codes', ['menu_id' => $menu->id]);
    }

    #[Test]
    public function download_endpoint_returns_png(): void
    {
        Storage::fake('public');

        $menu = Menu::factory()->create(['is_active' => true]);

        $action = new GenerateQrCode;
        $qr = $action->execute($menu);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.menus.qr-code.download', ['menu' => $menu->id]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'image/png');
    }

    #[Test]
    public function download_returns_404_when_no_qr_exists(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('tenant.menus.qr-code.download', ['menu' => $menu->id]));

        $response->assertStatus(404);
    }

    #[Test]
    public function destroy_endpoint_deletes_qr_code(): void
    {
        Storage::fake('public');

        $menu = Menu::factory()->create(['is_active' => true]);

        $action = new GenerateQrCode;
        $action->execute($menu);

        $this->assertDatabaseHas('qr_codes', ['menu_id' => $menu->id]);

        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.menus.qr-code.destroy', ['menu' => $menu->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('qr_codes', ['menu_id' => $menu->id]);
    }
}
