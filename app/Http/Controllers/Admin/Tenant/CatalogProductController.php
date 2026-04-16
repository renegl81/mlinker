<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Plan\CheckLimit;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\DuplicateProduct;
use App\Exceptions\PlanLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Section;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogProductController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureCatalog();

        $filters = $request->only(['q', 'menu_id', 'section_id', 'tag', 'allergen_id']);

        $query = Product::query()
            ->with(['menus:id,name', 'sections:id,name,menu_id', 'allergens:id,name,code']);

        if (! empty($filters['q'])) {
            $query->where('name', 'ilike', '%'.$filters['q'].'%');
        }
        if (! empty($filters['menu_id'])) {
            $query->whereHas('menus', fn ($q) => $q->where('menus.id', $filters['menu_id']));
        }
        if (! empty($filters['section_id'])) {
            $query->whereHas('sections', fn ($q) => $q->where('sections.id', $filters['section_id']));
        }
        if (! empty($filters['tag'])) {
            $query->whereJsonContains('tags', $filters['tag']);
        }
        if (! empty($filters['allergen_id'])) {
            $query->whereHas('allergens', fn ($q) => $q->where('allergens.id', $filters['allergen_id']));
        }

        $products = $query->orderBy('name')->paginate(25)->withQueryString();

        return Inertia::render('admin/tenant/catalog/Products', [
            'products' => $products,
            'menus' => Menu::orderBy('name')->get(['id', 'name']),
            'sections' => Section::orderBy('name')->get(['id', 'name', 'menu_id']),
            'allergens' => Allergen::orderBy('name')->get(['id', 'name', 'code']),
            'filters' => $filters,
        ]);
    }

    public function bulkDelete(Request $request, DeleteProduct $deleteProduct): RedirectResponse
    {
        $this->ensureCatalog();

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer'],
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])->get();

        foreach ($products as $product) {
            $deleteProduct->execute($product);
        }

        return back()->with('success', "{$products->count()} productos eliminados.");
    }

    public function bulkDuplicate(Request $request, DuplicateProduct $duplicateProduct): RedirectResponse
    {
        $this->ensureCatalog();

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer'],
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])->get();

        $checkLimit = new CheckLimit;
        $duplicated = 0;

        foreach ($products as $product) {
            try {
                $checkLimit->execute('products', throw: true);
            } catch (PlanLimitExceededException $e) {
                return back()->with('error', $e->getMessage());
            }
            $duplicateProduct->execute($product);
            $duplicated++;
        }

        return back()->with('success', "{$duplicated} productos duplicados.");
    }

    public function bulkAttachMenu(Request $request): RedirectResponse
    {
        $this->ensureCatalog();

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer'],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $section = Section::findOrFail($validated['section_id']);

        $maxMenuSort = DB::table('menu_product')
            ->where('menu_id', $menu->id)
            ->max('sort_order') ?? 0;

        $attached = 0;
        foreach ($validated['product_ids'] as $productId) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            DB::table('product_section')->insertOrIgnore([
                'product_id' => $product->id,
                'section_id' => $section->id,
                'tenant_id' => tenant('id'),
            ]);

            $exists = DB::table('menu_product')
                ->where('menu_id', $menu->id)
                ->where('product_id', $product->id)
                ->exists();

            if (! $exists) {
                $maxMenuSort++;
                DB::table('menu_product')->insert([
                    'menu_id' => $menu->id,
                    'product_id' => $product->id,
                    'tenant_id' => tenant('id'),
                    'sort_order' => $maxMenuSort,
                ]);
            }

            $attached++;
        }

        return back()->with('success', "{$attached} productos añadidos a '{$menu->name}'.");
    }

    private function ensureCatalog(): void
    {
        $tenantId = tenant()->id;

        $subscription = Subscription::where('tenant_id', $tenantId)
            ->latest()
            ->with('plan')
            ->first();

        $plan = $subscription?->plan ?? Plan::free();

        abort_unless((bool) ($plan?->has_catalog ?? false), 403, 'Catalog requires a Business plan or higher.');
    }
}
