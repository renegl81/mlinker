<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuStoreRequest;
use App\Http\Requests\MenuUpdateRequest;
use App\Jobs\CreateMenu;
use App\Jobs\DeleteMenu;
use App\Jobs\ListMenus;
use App\Jobs\ShowMenu;
use App\Jobs\UpdateMenu;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        ListMenus::dispatch();

        return Inertia::render('menu.index', [
            'menus' => $menus,
        ]);
    }

    public function store(MenuStoreRequest $request): RedirectResponse
    {
        CreateMenu::dispatch($request);

        return redirect()->route('menu.index');
    }

    public function show(Request $request, Menu $menu)
    {
        ShowMenu::dispatch($id);

        return Inertia::render('menu.show', [
            'menu' => $menu,
        ]);
    }

    public function update(MenuUpdateRequest $request, Menu $menu): RedirectResponse
    {
        UpdateMenu::dispatch($request, $id);

        return redirect()->route('menu.show', ['menu' => $menu]);
    }

    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        DeleteMenu::dispatch($id);

        return redirect()->route('menu.index');
    }
}
