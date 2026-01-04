<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Menu\CreateMenu;
use App\Actions\Menu\DeleteMenu;
use App\Actions\Menu\GetMenus;
use App\Actions\Menu\UpdateMenu;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuStoreRequest;
use App\Http\Requests\Menu\MenuUpdateRequest;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Template;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(Request $request, GetMenus $getMenus): Response
    {
        $menus = $getMenus->execute($request);
        return Inertia::render('admin/tenant/menus/Index', [
            'menus' => $menus,
            'filters' => $request->only(['name', 'is_active']),
        ]);
    }

    public function create(Location $location): Response
    {
        return Inertia::render('admin/tenant/menus/Create', [
            'location' => $location,
            'templates' => Template::all(),
        ]);
    }

    public function store(MenuStoreRequest $request, CreateMenu $createMenu): RedirectResponse
    {
        try{
            $createMenu->execute($request->validated());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }


        return redirect()
            ->route('tenant.locations.show', ['location' => $request->input('location_id')])
            ->with('success', __('messages.menus.created'));
    }

    public function edit(Menu $menu): Response
    {
        return Inertia::render('admin/tenant/menus/Edit', [
            'menu' => $menu,
        ]);
    }

    public function update(MenuUpdateRequest $request, Menu $menu, UpdateMenu $updateMenu): RedirectResponse
    {
        $updateMenu->execute($menu, $request->validated());

        return redirect()
            ->route('menus.index')
            ->with('success', __('messages.menus.updated'));
    }

    public function destroy(Menu $menu, DeleteMenu $deleteMenu): RedirectResponse
    {
        $deleteMenu->execute($menu);

        return redirect()
            ->route('menus.index')
            ->with('success', __('messages.menus.deleted'));
    }

    public function show(Location $location, Menu $menu): Response
    {
        return Inertia::render('admin/tenant/menus/Show', [
            'menu' => $menu,
        ]);

    }
}
