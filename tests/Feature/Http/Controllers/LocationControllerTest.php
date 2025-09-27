<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateLocation;
use App\Jobs\DeleteLocation;
use App\Jobs\ListLocations;
use App\Jobs\ShowLocation;
use App\Jobs\UpdateLocation;
use App\Models\;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\LocationController
 */
final class LocationControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('locations.index'));

        $response->assertOk();
        $response->assertViewIs('location.index');
        $response->assertViewHas('locations');

        Queue::assertPushed(ListLocations::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\LocationController::class,
            'store',
            \App\Http\Requests\LocationStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $address = fake()->word();
        $city = fake()->city();
        $province = fake()->word();
        $postal_code = fake()->postcode();
        $user = User::factory()->create();
        $country = ::factory()->create();

        Queue::fake();

        $response = $this->post(route('locations.store'), [
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postal_code,
            'user_id' => $user->id,
            'country_id' => $country->id,
        ]);

        $response->assertRedirect(route('location.index'));

        Queue::assertPushed(CreateLocation::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $location = Location::factory()->create();

        Queue::fake();

        $response = $this->get(route('locations.show', $location));

        $response->assertOk();
        $response->assertViewIs('location.show');
        $response->assertViewHas('location');

        Queue::assertPushed(ShowLocation::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\LocationController::class,
            'update',
            \App\Http\Requests\LocationUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $location = Location::factory()->create();
        $name = fake()->name();
        $address = fake()->word();
        $city = fake()->city();
        $province = fake()->word();
        $postal_code = fake()->postcode();
        $user = User::factory()->create();
        $country = ::factory()->create();

        Queue::fake();

        $response = $this->put(route('locations.update', $location), [
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postal_code,
            'user_id' => $user->id,
            'country_id' => $country->id,
        ]);

        $response->assertRedirect(route('location.show', ['location' => $location]));

        Queue::assertPushed(UpdateLocation::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $location = Location::factory()->create();

        Queue::fake();

        $response = $this->delete(route('locations.destroy', $location));

        $response->assertRedirect(route('location.index'));

        Queue::assertPushed(DeleteLocation::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
