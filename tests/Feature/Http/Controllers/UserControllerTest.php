<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateUser;
use App\Jobs\DeleteUser;
use App\Jobs\ListUsers;
use App\Jobs\ShowUser;
use App\Jobs\UpdateUser;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\UserController
 */
final class UserControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('users.index'));

        $response->assertOk();
        $response->assertViewIs('user.index');
        $response->assertViewHas('users');

        Queue::assertPushed(ListUsers::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\UserController::class,
            'store',
            \App\Http\Requests\UserStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $email = fake()->safeEmail();
        $password = fake()->password();
        $country = Country::factory()->create();

        Queue::fake();

        $response = $this->post(route('users.store'), [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'country_id' => $country->id,
        ]);

        $response->assertRedirect(route('user.index'));

        Queue::assertPushed(CreateUser::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $user = User::factory()->create();

        Queue::fake();

        $response = $this->get(route('users.show', $user));

        $response->assertOk();
        $response->assertViewIs('user.show');
        $response->assertViewHas('user');

        Queue::assertPushed(ShowUser::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\UserController::class,
            'update',
            \App\Http\Requests\UserUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $user = User::factory()->create();
        $name = fake()->name();
        $email = fake()->safeEmail();
        $password = fake()->password();
        $country = Country::factory()->create();

        Queue::fake();

        $response = $this->put(route('users.update', $user), [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'country_id' => $country->id,
        ]);

        $response->assertRedirect(route('user.show', ['user' => $user]));

        Queue::assertPushed(UpdateUser::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $user = User::factory()->create();

        Queue::fake();

        $response = $this->delete(route('users.destroy', $user));

        $response->assertRedirect(route('user.index'));

        Queue::assertPushed(DeleteUser::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
