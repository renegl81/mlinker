<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateCountry;
use App\Jobs\DeleteCountry;
use App\Jobs\ListCountries;
use App\Jobs\ShowCountry;
use App\Jobs\UpdateCountry;
use App\Models\Country;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CountryController
 */
final class CountryControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('countries.index'));

        $response->assertOk();
        $response->assertViewIs('country.index');
        $response->assertViewHas('countries');

        Queue::assertPushed(ListCountries::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CountryController::class,
            'store',
            \App\Http\Requests\CountryStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $code = fake()->word();

        Queue::fake();

        $response = $this->post(route('countries.store'), [
            'name' => $name,
            'code' => $code,
        ]);

        $response->assertRedirect(route('country.index'));

        Queue::assertPushed(CreateCountry::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $country = Country::factory()->create();

        Queue::fake();

        $response = $this->get(route('countries.show', $country));

        $response->assertOk();
        $response->assertViewIs('country.show');
        $response->assertViewHas('country');

        Queue::assertPushed(ShowCountry::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CountryController::class,
            'update',
            \App\Http\Requests\CountryUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $country = Country::factory()->create();
        $name = fake()->name();
        $code = fake()->word();

        Queue::fake();

        $response = $this->put(route('countries.update', $country), [
            'name' => $name,
            'code' => $code,
        ]);

        $response->assertRedirect(route('country.show', ['country' => $country]));

        Queue::assertPushed(UpdateCountry::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $country = Country::factory()->create();

        Queue::fake();

        $response = $this->delete(route('countries.destroy', $country));

        $response->assertRedirect(route('country.index'));

        Queue::assertPushed(DeleteCountry::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
