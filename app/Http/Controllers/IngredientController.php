<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientStoreRequest;
use App\Http\Requests\IngredientUpdateRequest;
use App\Jobs\CreateIngredient;
use App\Jobs\DeleteIngredient;
use App\Jobs\ListIngredients;
use App\Jobs\ShowIngredient;
use App\Jobs\UpdateIngredient;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        ListIngredients::dispatch();

        return Inertia::render('ingredient.index', [
            'ingredients' => $ingredients,
        ]);
    }

    public function store(IngredientStoreRequest $request): RedirectResponse
    {
        CreateIngredient::dispatch($request);

        return redirect()->route('ingredient.index');
    }

    public function show(Request $request, Ingredient $ingredient)
    {
        ShowIngredient::dispatch($id);

        return Inertia::render('ingredient.show', [
            'ingredient' => $ingredient,
        ]);
    }

    public function update(IngredientUpdateRequest $request, Ingredient $ingredient): RedirectResponse
    {
        UpdateIngredient::dispatch($request, $id);

        return redirect()->route('ingredient.show', ['ingredient' => $ingredient]);
    }

    public function destroy(Request $request, Ingredient $ingredient): RedirectResponse
    {
        DeleteIngredient::dispatch($id);

        return redirect()->route('ingredient.index');
    }
}
