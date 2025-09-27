<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateTemplate;
use App\Jobs\DeleteTemplate;
use App\Jobs\ListTemplates;
use App\Jobs\ShowTemplate;
use App\Jobs\UpdateTemplate;
use App\Models\Template;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\TemplateController
 */
final class TemplateControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('templates.index'));

        $response->assertOk();
        $response->assertViewIs('template.index');
        $response->assertViewHas('templates');

        Queue::assertPushed(ListTemplates::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TemplateController::class,
            'store',
            \App\Http\Requests\TemplateStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();

        Queue::fake();

        $response = $this->post(route('templates.store'), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('template.index'));

        Queue::assertPushed(CreateTemplate::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $template = Template::factory()->create();

        Queue::fake();

        $response = $this->get(route('templates.show', $template));

        $response->assertOk();
        $response->assertViewIs('template.show');
        $response->assertViewHas('template');

        Queue::assertPushed(ShowTemplate::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TemplateController::class,
            'update',
            \App\Http\Requests\TemplateUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $template = Template::factory()->create();
        $name = fake()->name();

        Queue::fake();

        $response = $this->put(route('templates.update', $template), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('template.show', ['template' => $template]));

        Queue::assertPushed(UpdateTemplate::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $template = Template::factory()->create();

        Queue::fake();

        $response = $this->delete(route('templates.destroy', $template));

        $response->assertRedirect(route('template.index'));

        Queue::assertPushed(DeleteTemplate::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
