<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateMenuCard;
use App\Jobs\DeleteMenuCard;
use App\Jobs\ListMenuCards;
use App\Jobs\ShowMenuCard;
use App\Jobs\UpdateMenuCard;
use App\Models\;
use App\Models\Location;
use App\Models\MenuCard;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\MenuCardController
 */
final class MenuCardControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('menu-cards.index'));

        $response->assertOk();
        $response->assertViewIs('menucard.index');
        $response->assertViewHas('menucards');

        Queue::assertPushed(ListMenuCards::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\MenuCardController::class,
            'store',
            \App\Http\Requests\MenuCardStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $location = Location::factory()->create();
        $template = ::factory()->create();

        Queue::fake();

        $response = $this->post(route('menu-cards.store'), [
            'name' => $name,
            'location_id' => $location->id,
            'template_id' => $template->id,
        ]);

        $response->assertRedirect(route('menucard.index'));

        Queue::assertPushed(CreateMenuCard::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $menuCard = MenuCard::factory()->create();

        Queue::fake();

        $response = $this->get(route('menu-cards.show', $menuCard));

        $response->assertOk();
        $response->assertViewIs('menucard.show');
        $response->assertViewHas('menucard');

        Queue::assertPushed(ShowMenuCard::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\MenuCardController::class,
            'update',
            \App\Http\Requests\MenuCardUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $menuCard = MenuCard::factory()->create();
        $name = fake()->name();
        $location = Location::factory()->create();
        $template = ::factory()->create();

        Queue::fake();

        $response = $this->put(route('menu-cards.update', $menuCard), [
            'name' => $name,
            'location_id' => $location->id,
            'template_id' => $template->id,
        ]);

        $response->assertRedirect(route('menucard.show', ['menucard' => $menucard]));

        Queue::assertPushed(UpdateMenuCard::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $menuCard = MenuCard::factory()->create();

        Queue::fake();

        $response = $this->delete(route('menu-cards.destroy', $menuCard));

        $response->assertRedirect(route('menucard.index'));

        Queue::assertPushed(DeleteMenuCard::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
