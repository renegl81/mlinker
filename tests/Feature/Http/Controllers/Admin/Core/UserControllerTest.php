<?php

namespace Tests\Feature\Http\Controllers\Admin\Core;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#Test
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario administrador para autenticación
        $this->admin = User::factory()->create();
    }

    #[Test]
    public function it_displays_users_index_page()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->component('admin/users/Index')
            ->has('users.data', 6) // 5 + admin
        );
    }

    #[Test]
    public function it_can_search_users()
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)
            ->get(route('users.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->has('users.data', 1)
            ->where('users.data.0.name', 'John Doe')
        );
    }

    #[Test]
    public function it_displays_create_user_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('users.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->component('admin/users/Create')
        );
    }

    #[Test]
    public function it_can_store_a_new_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_storing()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_validates_email_uniqueness_when_storing()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_validates_password_confirmation_when_storing()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function it_displays_edit_user_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('users.edit', $user));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->component('admin/users/Edit')
            ->where('user.id', $user->id)
        );
    }

    #[Test]
    public function it_can_update_user_without_password()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'New Name',
                'email' => $user->email,
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }

    #[Test]
    public function it_can_update_user_with_password()
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertNotEquals($oldPassword, $user->password);
    }

    #[Test]
    public function it_validates_email_uniqueness_when_updating()
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'last_name' => 'User 1',
            'password' => 'password'
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user1), [
                'name' => $user1->name,
                'last_name' => $user1->last_name,
                'email' => $user1->email,
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_can_delete_a_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
