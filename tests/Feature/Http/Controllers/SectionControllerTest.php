<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateSection;
use App\Jobs\DeleteSection;
use App\Jobs\ListSections;
use App\Jobs\ShowSection;
use App\Jobs\UpdateSection;
use App\Models\Menu;
use App\Models\Section;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\SectionController
 */
final class SectionControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('sections.index'));

        $response->assertOk();
        $response->assertViewIs('section.index');
        $response->assertViewHas('sections');

        Queue::assertPushed(ListSections::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SectionController::class,
            'store',
            \App\Http\Requests\SectionStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $menu = Menu::factory()->create();

        Queue::fake();

        $response = $this->post(route('sections.store'), [
            'name' => $name,
            'menu_id' => $menu->id,
        ]);

        $response->assertRedirect(route('section.index'));

        Queue::assertPushed(CreateSection::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $section = Section::factory()->create();

        Queue::fake();

        $response = $this->get(route('sections.show', $section));

        $response->assertOk();
        $response->assertViewIs('section.show');
        $response->assertViewHas('section');

        Queue::assertPushed(ShowSection::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SectionController::class,
            'update',
            \App\Http\Requests\SectionUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $section = Section::factory()->create();
        $name = fake()->name();
        $menu = Menu::factory()->create();

        Queue::fake();

        $response = $this->put(route('sections.update', $section), [
            'name' => $name,
            'menu_id' => $menu->id,
        ]);

        $response->assertRedirect(route('section.show', ['section' => $section]));

        Queue::assertPushed(UpdateSection::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $section = Section::factory()->create();

        Queue::fake();

        $response = $this->delete(route('sections.destroy', $section));

        $response->assertRedirect(route('section.index'));

        Queue::assertPushed(DeleteSection::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
