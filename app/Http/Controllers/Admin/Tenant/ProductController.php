<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Plan\CheckLimit;
use App\Actions\Product\CreateProduct;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\DuplicateProduct;
use App\Actions\Product\UpdateProduct;
use App\Exceptions\PlanLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Section;
use App\Services\IngredientCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function create(Request $request, Menu $menu, IngredientCatalog $catalog): Response
    {
        $menu->load('sections');

        // If no sections exist, create a default one
        if ($menu->sections->isEmpty()) {
            $section = Section::create([
                'name' => 'General',
                'menu_id' => $menu->id,
                'tenant_id' => tenant('id'),
                'sort_order' => 1,
            ]);
            $menu->sections = collect([$section]);
        }

        return Inertia::render('admin/tenant/products/Create', [
            'menu' => $menu,
            'sections' => $menu->sections,
            'allergens' => Allergen::orderBy('name')->get(),
            'ingredients' => Ingredient::orderBy('name')->get(),
            'catalogIngredients' => $catalog->popular(),
            'defaultSectionId' => $request->query('section_id'),
        ]);
    }

    public function store(StoreProductRequest $request, Menu $menu, CreateProduct $createProduct): RedirectResponse
    {
        try {
            (new CheckLimit)->execute('products', throw: true);
        } catch (PlanLimitExceededException $e) {
            return back()->with('error', $e->getMessage());
        }

        $createProduct->execute($menu, $request->validated());

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu->id])
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product, IngredientCatalog $catalog): Response
    {
        $product->load(['sections.menu', 'allergens', 'ingredients']);

        $menu = $product->sections->first()?->menu;

        return Inertia::render('admin/tenant/products/Edit', [
            'product' => $product,
            'menu' => $menu,
            'sections' => $menu ? Section::where('menu_id', $menu->id)->orderBy('sort_order')->get() : collect(),
            'allergens' => Allergen::orderBy('name')->get(),
            'ingredients' => Ingredient::orderBy('name')->get(),
            'catalogIngredients' => $catalog->popular(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $updateProduct): RedirectResponse
    {
        $updateProduct->execute($product, $request->validated());

        $menu = $product->sections()->first()?->menu
            ?? $product->menus()->first();

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu?->id])
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product, DeleteProduct $deleteProduct): RedirectResponse
    {
        $menu = $product->sections()->first()?->menu
            ?? $product->menus()->first();

        $deleteProduct->execute($product);

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu?->id])
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function duplicate(Product $product, DuplicateProduct $duplicateProduct): RedirectResponse
    {
        try {
            (new CheckLimit)->execute('products', throw: true);
        } catch (PlanLimitExceededException $e) {
            return back()->with('error', $e->getMessage());
        }

        $product->load(['sections.menu']);
        $menu = $product->sections->first()?->menu
            ?? $product->menus()->first();

        $duplicateProduct->execute($product);

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu?->id])
            ->with('success', 'Producto duplicado correctamente.');
    }
}
