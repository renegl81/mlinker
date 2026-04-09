<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Plan\CheckLimit;
use App\Exceptions\PlanLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request, Menu $menu): RedirectResponse
    {
        try {
            (new CheckLimit)->execute('products', throw: true);
        } catch (PlanLimitExceededException $e) {
            return back()->with('error', $e->getMessage());
        }

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store(
                'products',
                'public'
            );
        }

        $validated['tenant_id'] = tenant()->id;

        $product = Product::create($validated);

        $menu->products()->attach($product->id, [
            'sort_order' => $menu->products()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Producto creado exitosamente.');
    }

    public function update(
        UpdateProductRequest $request,
        Menu $menu,
        Product $product
    ): RedirectResponse {
        $this->authorize('update', $product);

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }

            $validated['image_url'] = $request->file('image')->store(
                'products',
                'public'
            );
        }

        $product->update($validated);

        return back()->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Menu $menu, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $menu->products()->detach($product->id);

        if ($product->menus()->count() === 0) {
            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }

            $product->delete();
        }

        return back()->with(
            'success',
            'Producto eliminado del menú exitosamente.'
        );
    }
}
