<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateTranslation;
use App\Jobs\DeleteTranslation;
use App\Jobs\ListTranslations;
use App\Jobs\ShowTranslation;
use App\Jobs\UpdateTranslation;
use App\Models\Translatable;
use App\Models\Translation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\TranslationController
 */
final class TranslationControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('translations.index'));

        $response->assertOk();
        $response->assertViewIs('translation.index');
        $response->assertViewHas('translations');

        Queue::assertPushed(ListTranslations::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TranslationController::class,
            'store',
            \App\Http\Requests\TranslationStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $translatable = Translatable::factory()->create();
        $translatable_type = fake()->word();
        $locale = fake()->word();
        $field = fake()->word();
        $value = fake()->word();

        Queue::fake();

        $response = $this->post(route('translations.store'), [
            'translatable_id' => $translatable->id,
            'translatable_type' => $translatable_type,
            'locale' => $locale,
            'field' => $field,
            'value' => $value,
        ]);

        $response->assertRedirect(route('translation.index'));

        Queue::assertPushed(CreateTranslation::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $translation = Translation::factory()->create();

        Queue::fake();

        $response = $this->get(route('translations.show', $translation));

        $response->assertOk();
        $response->assertViewIs('translation.show');
        $response->assertViewHas('translation');

        Queue::assertPushed(ShowTranslation::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TranslationController::class,
            'update',
            \App\Http\Requests\TranslationUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $translation = Translation::factory()->create();
        $translatable = Translatable::factory()->create();
        $translatable_type = fake()->word();
        $locale = fake()->word();
        $field = fake()->word();
        $value = fake()->word();

        Queue::fake();

        $response = $this->put(route('translations.update', $translation), [
            'translatable_id' => $translatable->id,
            'translatable_type' => $translatable_type,
            'locale' => $locale,
            'field' => $field,
            'value' => $value,
        ]);

        $response->assertRedirect(route('translation.show', ['translation' => $translation]));

        Queue::assertPushed(UpdateTranslation::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $translation = Translation::factory()->create();

        Queue::fake();

        $response = $this->delete(route('translations.destroy', $translation));

        $response->assertRedirect(route('translation.index'));

        Queue::assertPushed(DeleteTranslation::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
