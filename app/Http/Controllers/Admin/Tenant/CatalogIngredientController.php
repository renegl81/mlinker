<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Ingredient\MergeIngredients;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CatalogIngredientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureCatalog();

        $filters = $request->only(['q']);

        $query = Ingredient::query()->withCount('products');

        if (! empty($filters['q'])) {
            $query->where('name', 'ilike', '%'.$filters['q'].'%');
        }

        $ingredients = $query->orderBy('name')->paginate(50)->withQueryString();

        return Inertia::render('admin/tenant/catalog/Ingredients', [
            'ingredients' => $ingredients,
            'supportedLocales' => config('menulinker.supported_locales'),
            'sourceLocale' => config('menulinker.source_locale', 'es'),
            'filters' => $filters,
        ]);
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->ensureCatalog();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ingredients', 'name')
                    ->where(fn ($q) => $q->where('tenant_id', tenant('id')))
                    ->ignore($ingredient->id),
            ],
        ]);

        $ingredient->update(['name' => $validated['name']]);

        return back()->with('success', 'Ingrediente actualizado.');
    }

    public function updateTranslations(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->ensureCatalog();

        $validated = $request->validate([
            'translations' => ['required', 'array'],
        ]);

        $ingredient->update(['translations' => $validated['translations']]);

        return back()->with('success', 'Traducciones actualizadas.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        $this->ensureCatalog();

        DB::table('ingredient_product')->where('ingredient_id', $ingredient->id)->delete();
        $ingredient->delete();

        return back()->with('success', 'Ingrediente eliminado.');
    }

    public function merge(Request $request, MergeIngredients $mergeIngredients): RedirectResponse
    {
        $this->ensureCatalog();

        $validated = $request->validate([
            'ingredient_ids' => ['required', 'array', 'min:2'],
            'ingredient_ids.*' => ['integer'],
            'survivor_id' => ['required', 'integer'],
        ]);

        // Enforce tenant isolation: all ids must belong to current tenant.
        $found = Ingredient::whereIn('id', $validated['ingredient_ids'])->pluck('id')->all();
        if (count($found) !== count($validated['ingredient_ids'])) {
            abort(404, 'Some ingredients do not belong to this tenant.');
        }

        try {
            $mergeIngredients->execute($validated['ingredient_ids'], $validated['survivor_id']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ingredientes fusionados correctamente.');
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
