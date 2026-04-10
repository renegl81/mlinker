<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Section;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TranslationController extends Controller
{
    public function show(Menu $menu): Response
    {
        $hasMultilang = $this->tenantHasMultilang();

        $menu->load([
            'sections' => fn ($q) => $q->orderBy('id'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
        ]);

        return Inertia::render('admin/tenant/menus/Translations', [
            'menu' => $menu,
            'hasMultilang' => $hasMultilang,
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse|JsonResponse
    {
        if (! $this->tenantHasMultilang()) {
            abort(403, 'Multi-language translations require a Business plan or higher.');
        }

        $validated = $request->validate([
            'menu' => ['sometimes', 'array'],
            'menu.translations' => ['sometimes', 'array'],
            'sections' => ['sometimes', 'array'],
            'sections.*.id' => ['required_with:sections', 'integer'],
            'sections.*.translations' => ['required_with:sections', 'array'],
            'products' => ['sometimes', 'array'],
            'products.*.id' => ['required_with:products', 'integer'],
            'products.*.translations' => ['required_with:products', 'array'],
        ]);

        // Update menu translations
        if (isset($validated['menu']['translations'])) {
            $menu->update(['translations' => $validated['menu']['translations']]);
        }

        // Update section translations
        if (! empty($validated['sections'])) {
            foreach ($validated['sections'] as $sectionData) {
                Section::where('id', $sectionData['id'])
                    ->where('tenant_id', tenant()->id)
                    ->update(['translations' => $sectionData['translations']]);
            }
        }

        // Update product translations
        if (! empty($validated['products'])) {
            foreach ($validated['products'] as $productData) {
                Product::where('id', $productData['id'])
                    ->where('tenant_id', tenant()->id)
                    ->update(['translations' => $productData['translations']]);
            }
        }

        return back()->with('success', 'Traducciones guardadas correctamente.');
    }

    private function tenantHasMultilang(): bool
    {
        $tenantId = tenant()->id;

        $subscription = Subscription::where('tenant_id', $tenantId)
            ->latest()
            ->with('plan')
            ->first();

        $plan = $subscription?->plan ?? Plan::free();

        return (bool) ($plan?->has_multilang ?? false);
    }
}
