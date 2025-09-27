<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateMenu;
use App\Jobs\DeleteMenu;
use App\Jobs\ListMenus;
use App\Jobs\ShowMenu;
use App\Jobs\UpdateMenu;
use App\Models\Menu;
use App\Models\MenuCard;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\MenuController
 */
final class MenuControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('menus.index'));

        $response->assertOk();
        $response->assertViewIs('menu.index');
        $response->assertViewHas('menus');

        Queue::assertPushed(ListMenus::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\MenuController::class,
            'store',
            \App\Http\Requests\MenuStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $menu_card = MenuCard::factory()->create();

        Queue::fake();

        $response = $this->post(route('menus.store'), [
            'name' => $name,
            'menu_card_id' => $menu_card->id,
        ]);

        $response->assertRedirect(route('menu.index'));

        Queue::assertPushed(CreateMenu::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $menu = Menu::factory()->create();

        Queue::fake();

        $response = $this->get(route('menus.show', $menu));

        $response->assertOk();
        $response->assertViewIs('menu.show');
        $response->assertViewHas('menu');

        Queue::assertPushed(ShowMenu::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\MenuController::class,
            'update',
            \App\Http\Requests\MenuUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $menu = Menu::factory()->create();
        $name = fake()->name();
        $menu_card = MenuCard::factory()->create();

        Queue::fake();

        $response = $this->put(route('menus.update', $menu), [
            'name' => $name,
            'menu_card_id' => $menu_card->id,
        ]);

        $response->assertRedirect(route('menu.show', ['menu' => $menu]));

        Queue::assertPushed(UpdateMenu::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $menu = Menu::factory()->create();

        Queue::fake();

        $response = $this->delete(route('menus.destroy', $menu));

        $response->assertRedirect(route('menu.index'));

        Queue::assertPushed(DeleteMenu::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
