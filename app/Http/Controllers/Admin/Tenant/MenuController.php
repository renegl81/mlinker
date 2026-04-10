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
        $menu->load('location');

        return Inertia::render('admin/tenant/menus/Edit', [
            'menu' => $menu,
            'location' => $menu->location,
            'templates' => Template::where('is_active', true)->get(),
        ]);
    }

    public function update(MenuUpdateRequest $request, Menu $menu, UpdateMenu $updateMenu): RedirectResponse
    {
        $updateMenu->execute($menu, $request->validated());

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu->id])
            ->with('success', __('messages.menus.updated'));
    }

    public function destroy(Menu $menu, DeleteMenu $deleteMenu): RedirectResponse
    {
        $locationId = $menu->location_id;
        $deleteMenu->execute($menu);

        return redirect()
            ->route('tenant.locations.menus.index', ['location' => $locationId])
            ->with('success', __('messages.menus.deleted'));
    }

    public function show(Menu $menu): Response
    {
        $menu->load([
            'location',
            'template',
            'sections' => fn ($q) => $q->orderBy('id'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
            'qrCode',
        ]);

        return Inertia::render('admin/tenant/menus/Show', [
            'menu' => $menu,
            'qrCodeImageUrl' => $this->resolveQrImageUrl($menu),
        ]);
    }

    /**
     * Build a public URL for the QR image stored on the tenant's disk.
     *
     * Stancl's FilesystemTenancyBootstrapper re-roots the public disk under
     * storage/tenant{id}/app/public/, so Storage::url() does not resolve
     * correctly through the public/storage symlink. We serve the file via
     * the tenant_image route (TenantImageController) instead.
     */
    protected function resolveQrImageUrl(Menu $menu): ?string
    {
        $imagePath = $menu->qrCode?->image_url;

        if (! $imagePath) {
            return null;
        }

        $tenantId = tenant('id');

        if (! $tenantId) {
            return null;
        }

        return rtrim(config('app.url'), '/').route(
            'tenant_image',
            ['tenant' => 'tenant'.$tenantId, 'path' => $imagePath],
            false,
        );
    }
}
