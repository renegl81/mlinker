<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Menu\CreateLocation;
use App\Actions\Menu\DeleteLocation;
use App\Actions\Menu\ListLocations;
use App\Actions\Menu\UpdateLocation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuStoreRequest;
use App\Http\Requests\Menu\MenuUpdateRequest;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(Request $request): Response
    {
        $menus = app(ListLocations::class)($request);

        return Inertia::render('admin/tenant/Menus/Index', [
            'menus' => $menus,
            'filters' => $request->only(['name', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/tenant/Menus/Create');
    }

    public function store(MenuStoreRequest $request): RedirectResponse
    {
        app(CreateLocation::class)($request->validated());

        return redirect()
            ->route('menus.index')
            ->with('success', __('messages.menus.created'));
    }

    public function edit(Menu $menu): Response
    {
        return Inertia::render('admin/tenant/Menus/Edit', [
            'menu' => $menu,
        ]);
    }

    public function update(MenuUpdateRequest $request, Menu $menu): RedirectResponse
    {
        app(UpdateLocation::class)($menu, $request->validated());

        return redirect()
            ->route('menus.index')
            ->with('success', __('messages.menus.updated'));
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        app(DeleteLocation::class)($menu);

        return redirect()
            ->route('menus.index')
            ->with('success', __('messages.menus.deleted'));
    }
}
