<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateIngredient;
use App\Jobs\DeleteIngredient;
use App\Jobs\ListIngredients;
use App\Jobs\ShowIngredient;
use App\Jobs\UpdateIngredient;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\IngredientController
 */
final class IngredientControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('ingredients.index'));

        $response->assertOk();
        $response->assertViewIs('ingredient.index');
        $response->assertViewHas('ingredients');

        Queue::assertPushed(ListIngredients::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\IngredientController::class,
            'store',
            \App\Http\Requests\IngredientStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();

        Queue::fake();

        $response = $this->post(route('ingredients.store'), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('ingredient.index'));

        Queue::assertPushed(CreateIngredient::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $ingredient = Ingredient::factory()->create();

        Queue::fake();

        $response = $this->get(route('ingredients.show', $ingredient));

        $response->assertOk();
        $response->assertViewIs('ingredient.show');
        $response->assertViewHas('ingredient');

        Queue::assertPushed(ShowIngredient::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\IngredientController::class,
            'update',
            \App\Http\Requests\IngredientUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $ingredient = Ingredient::factory()->create();
        $name = fake()->name();

        Queue::fake();

        $response = $this->put(route('ingredients.update', $ingredient), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('ingredient.show', ['ingredient' => $ingredient]));

        Queue::assertPushed(UpdateIngredient::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $ingredient = Ingredient::factory()->create();

        Queue::fake();

        $response = $this->delete(route('ingredients.destroy', $ingredient));

        $response->assertRedirect(route('ingredient.index'));

        Queue::assertPushed(DeleteIngredient::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
