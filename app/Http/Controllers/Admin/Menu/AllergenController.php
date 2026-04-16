<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllergenStoreRequest;
use App\Http\Requests\AllergenUpdateRequest;
use App\Jobs\CreateAllergen;
use App\Jobs\DeleteAllergen;
use App\Jobs\ListAllergens;
use App\Jobs\ShowAllergen;
use App\Jobs\UpdateAllergen;
use App\Models\Allergen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AllergenController extends Controller
{
    public function index(Request $request)
    {
        $allergens = ListAllergens::dispatch();

        return Inertia::render('allergen.index', [
            'allergens' => $allergens,
        ]);
    }

    public function store(AllergenStoreRequest $request): RedirectResponse
    {
        CreateAllergen::dispatch($request);

        return redirect()->route('allergen.index');
    }

    public function show(Request $request, Allergen $allergen): Response
    {
        ShowAllergen::dispatch($allergen);

        return Inertia::render('allergen.show', [
            'allergen' => $allergen,
        ]);
    }

    public function update(AllergenUpdateRequest $request, Allergen $allergen): RedirectResponse
    {
        UpdateAllergen::dispatch($request, $allergen);

        return redirect()->route('allergen.show', ['allergen' => $allergen]);
    }

    public function destroy(Request $request, Allergen $allergen): RedirectResponse
    {
        DeleteAllergen::dispatch($allergen);

        return redirect()->route('allergen.index');
    }
}
