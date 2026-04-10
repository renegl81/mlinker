<?php

namespace Tests\Feature\Mail;

use App\Events\MenuActivated;
use App\Mail\MenuPublishedMail;
use App\Mail\TenantInvitationMail;
use App\Mail\WelcomeMail;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class MailableTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

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
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }

    protected function tenantUrl(string $routeName, array $params = []): string
    {
        $path = route($routeName, $params, false);

        return 'http://test.localhost'.$path;
    }

    #[Test]
    public function welcome_mail_is_sent_when_onboarding_is_completed(): void
    {
        Mail::fake();
        Storage::fake('public');
        Event::fake([TenantCreated::class, TenantDeleted::class, MenuActivated::class]);

        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'tenant_id' => 'test-tenant',
            'is_active' => true,
        ]);

        $this->tenant->onboarding_step = 3;
        $this->tenant->save();

        $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.onboarding.complete'), [
                'menu_id' => $menu->id,
            ]);

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    #[Test]
    public function welcome_mail_builds_correctly(): void
    {
        $mailable = new WelcomeMail(
            user: $this->user,
            menuUrl: 'http://test.localhost/m/1',
            qrDownloadUrl: 'http://test.localhost/storage/qr.png',
        );

        $this->assertSame('¡Tu menú digital está listo!', $mailable->envelope()->subject);
        $this->assertSame('mail.welcome', $mailable->content()->view);
        $this->assertSame($this->user, $mailable->user);
        $this->assertSame('http://test.localhost/m/1', $mailable->menuUrl);
        $this->assertSame('http://test.localhost/storage/qr.png', $mailable->qrDownloadUrl);
    }

    #[Test]
    public function tenant_invitation_mail_builds_correctly(): void
    {
        $mailable = new TenantInvitationMail(
            tenantName: 'Cafetería El Sol',
            inviterName: 'María García',
            role: 'Administrador',
            invitationUrl: 'http://test.localhost/invitation/abc123',
        );

        $this->assertSame('Cafetería El Sol', $mailable->tenantName);
        $this->assertSame('María García', $mailable->inviterName);
        $this->assertSame('Administrador', $mailable->role);
        $this->assertSame('http://test.localhost/invitation/abc123', $mailable->invitationUrl);
        $this->assertSame('mail.tenant-invitation', $mailable->content()->view);
    }

    #[Test]
    public function tenant_invitation_mail_has_correct_subject(): void
    {
        $mailable = new TenantInvitationMail(
            tenantName: 'Restaurante La Plaza',
            inviterName: 'Juan',
            role: 'Gestor',
            invitationUrl: 'http://example.com/invite',
        );

        $this->assertSame('Te han invitado a Restaurante La Plaza', $mailable->envelope()->subject);
    }

    #[Test]
    public function menu_published_mail_builds_correctly(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create([
            'name' => 'Carta de Verano',
            'location_id' => $location->id,
            'tenant_id' => 'test-tenant',
        ]);

        $mailable = new MenuPublishedMail(
            user: $this->user,
            menu: $menu,
            publicUrl: 'http://test.localhost/m/1',
        );

        $this->assertSame($this->user, $mailable->user);
        $this->assertSame($menu->id, $mailable->menu->id);
        $this->assertSame('http://test.localhost/m/1', $mailable->publicUrl);
        $this->assertSame('mail.menu-published', $mailable->content()->view);
    }

    #[Test]
    public function menu_published_mail_has_correct_subject(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create([
            'name' => 'Carta de Invierno',
            'location_id' => $location->id,
            'tenant_id' => 'test-tenant',
        ]);

        $mailable = new MenuPublishedMail(
            user: $this->user,
            menu: $menu,
            publicUrl: 'http://test.localhost/m/1',
        );

        $this->assertSame("Tu menú 'Carta de Invierno' está publicado", $mailable->envelope()->subject);
    }

    #[Test]
    public function activating_a_menu_dispatches_menu_activated_event(): void
    {
        Event::fake([TenantCreated::class, TenantDeleted::class, MenuActivated::class]);

        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'tenant_id' => 'test-tenant',
            'is_active' => false,
        ]);

        $menu->is_active = true;
        $menu->save();

        Event::assertDispatched(MenuActivated::class, function (MenuActivated $event) use ($menu) {
            return $event->menu->id === $menu->id;
        });
    }

    #[Test]
    public function updating_menu_without_changing_is_active_does_not_dispatch_event(): void
    {
        Event::fake([TenantCreated::class, TenantDeleted::class, MenuActivated::class]);

        $location = Location::factory()->create(['tenant_id' => 'test-tenant']);
        $menu = Menu::factory()->create([
            'location_id' => $location->id,
            'tenant_id' => 'test-tenant',
            'is_active' => true,
        ]);

        // Update something else, not is_active
        $menu->name = 'Nuevo nombre';
        $menu->save();

        Event::assertNotDispatched(MenuActivated::class);
    }
}
