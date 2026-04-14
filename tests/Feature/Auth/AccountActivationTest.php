<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class AccountActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);
        Role::query()->firstOrCreate(['name' => 'Owner']);
        Plan::factory()->create(['slug' => 'free']);
    }

    #[Test]
    public function activation_link_marks_user_as_active_and_verified(): void
    {
        $tenant = Tenant::create(['id' => 'bar-test']);
        $tenant->domains()->create(['domain' => 'bar-test.lvh.me']);

        $user = User::factory()->create([
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        $user->tenants()->attach($tenant->id, [
            'role' => 'owner',
            'is_active' => false,
        ]);

        $activationUrl = URL::temporarySignedRoute(
            'auth.activate',
            now()->addHours(24),
            ['user' => $user->id]
        );

        $response = $this->get($activationUrl);

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);

        // Should redirect to tenant panel, not to login
        $response->assertRedirect();
        $this->assertStringContainsString('bar-test.lvh.me', $response->headers->get('Location'));
        $this->assertStringContainsString('/panel', $response->headers->get('Location'));
    }

    #[Test]
    public function activation_link_with_invalid_signature_returns_403(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->get(route('auth.activate', ['user' => $user->id]));

        $response->assertStatus(403);
    }

    #[Test]
    public function already_active_user_is_redirected_to_login(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $activationUrl = URL::temporarySignedRoute(
            'auth.activate',
            now()->addHours(24),
            ['user' => $user->id]
        );

        $response = $this->get($activationUrl);

        $response->assertRedirect(route('login'));
    }
}
