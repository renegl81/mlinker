<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Menu\CreateMenu;
use App\Actions\Menu\DeleteMenu;
use App\Actions\Menu\GetMenus;
use App\Actions\Menu\UpdateMenu;
use App\Actions\Plan\CheckLimit;
use App\Exceptions\PlanLimitExceededException;
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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(Request $request, Location $location, GetMenus $getMenus): Response
    {
        $menus = $getMenus->execute($request);

        return Inertia::render('admin/tenant/menus/Index', [
            'location' => $location,
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
        try {
            (new CheckLimit)->execute('menus', throw: true);
            $createMenu->execute($request->validated());
        } catch (PlanLimitExceededException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error: '.$e->getMessage());
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

    public function show(Menu $menu): Response
    {
        return Inertia::render('admin/tenant/menus/Show', [
            'menu' => $menu->load(['location', 'template', 'sections', 'qrCode']),
            'qrCodeImageUrl' => $menu->qrCode?->image_url
                ? Storage::disk('public')->url($menu->qrCode->image_url)
                : null,
        ]);

    }
}
