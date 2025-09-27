<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateOpeningHour;
use App\Jobs\DeleteOpeningHour;
use App\Jobs\ListOpeningHours;
use App\Jobs\ShowOpeningHour;
use App\Jobs\UpdateOpeningHour;
use App\Models\Location;
use App\Models\OpeningHour;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\OpeningHourController
 */
final class OpeningHourControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('opening-hours.index'));

        $response->assertOk();
        $response->assertViewIs('openinghour.index');
        $response->assertViewHas('openinghours');

        Queue::assertPushed(ListOpeningHours::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\OpeningHourController::class,
            'store',
            \App\Http\Requests\OpeningHourStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $location = Location::factory()->create();
        $weekday = fake()->numberBetween(-10000, 10000);

        Queue::fake();

        $response = $this->post(route('opening-hours.store'), [
            'location_id' => $location->id,
            'weekday' => $weekday,
        ]);

        $response->assertRedirect(route('openinghour.index'));

        Queue::assertPushed(CreateOpeningHour::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $openingHour = OpeningHour::factory()->create();

        Queue::fake();

        $response = $this->get(route('opening-hours.show', $openingHour));

        $response->assertOk();
        $response->assertViewIs('openinghour.show');
        $response->assertViewHas('openinghour');

        Queue::assertPushed(ShowOpeningHour::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\OpeningHourController::class,
            'update',
            \App\Http\Requests\OpeningHourUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $openingHour = OpeningHour::factory()->create();
        $location = Location::factory()->create();
        $weekday = fake()->numberBetween(-10000, 10000);

        Queue::fake();

        $response = $this->put(route('opening-hours.update', $openingHour), [
            'location_id' => $location->id,
            'weekday' => $weekday,
        ]);

        $response->assertRedirect(route('openinghour.show', ['openinghour' => $openinghour]));

        Queue::assertPushed(UpdateOpeningHour::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $openingHour = OpeningHour::factory()->create();

        Queue::fake();

        $response = $this->delete(route('opening-hours.destroy', $openingHour));

        $response->assertRedirect(route('openinghour.index'));

        Queue::assertPushed(DeleteOpeningHour::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
