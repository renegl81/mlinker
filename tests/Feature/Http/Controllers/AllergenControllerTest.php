<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateAllergen;
use App\Jobs\DeleteAllergen;
use App\Jobs\ListAllergens;
use App\Jobs\ShowAllergen;
use App\Jobs\UpdateAllergen;
use App\Models\Allergen;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\AllergenController
 */
final class AllergenControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('allergens.index'));

        $response->assertOk();
        $response->assertViewIs('allergen.index');
        $response->assertViewHas('allergens');

        Queue::assertPushed(ListAllergens::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\AllergenController::class,
            'store',
            \App\Http\Requests\AllergenStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();

        Queue::fake();

        $response = $this->post(route('allergens.store'), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('allergen.index'));

        Queue::assertPushed(CreateAllergen::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $allergen = Allergen::factory()->create();

        Queue::fake();

        $response = $this->get(route('allergens.show', $allergen));

        $response->assertOk();
        $response->assertViewIs('allergen.show');
        $response->assertViewHas('allergen');

        Queue::assertPushed(ShowAllergen::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\AllergenController::class,
            'update',
            \App\Http\Requests\AllergenUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $allergen = Allergen::factory()->create();
        $name = fake()->name();

        Queue::fake();

        $response = $this->put(route('allergens.update', $allergen), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('allergen.show', ['allergen' => $allergen]));

        Queue::assertPushed(UpdateAllergen::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $allergen = Allergen::factory()->create();

        Queue::fake();

        $response = $this->delete(route('allergens.destroy', $allergen));

        $response->assertRedirect(route('allergen.index'));

        Queue::assertPushed(DeleteAllergen::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
