<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Actions\Menu\GetMenus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MenuStoreRequest;
use App\Http\Requests\MenuUpdateRequest;
use App\Jobs\CreateMenu;
use App\Jobs\DeleteMenu;
use App\Jobs\ShowMenu;
use App\Jobs\UpdateMenu;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request, GetMenus $getMenus)
    {
        $menus = $getMenus->execute($request);

        return Inertia::render('Menu.index', [
            'menus' => $menus,
        ]);
    }

    public function store(MenuStoreRequest $request): RedirectResponse
    {
        CreateMenu::dispatch($request);

        return redirect()->route('Menu.index');
    }

    public function show(Request $request, Menu $menu)
    {
        ShowMenu::dispatch($id);

        return Inertia::render('Menu.show', [
            'Menu' => $menu,
        ]);
    }

    public function update(MenuUpdateRequest $request, Menu $menu): RedirectResponse
    {
        UpdateMenu::dispatch($request, $id);

        return redirect()->route('Menu.show', ['Menu' => $menu]);
    }

    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        DeleteMenu::dispatch($id);

        return redirect()->route('Menu.index');
    }
}
