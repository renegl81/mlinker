<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuCardStoreRequest;
use App\Http\Requests\MenuCardUpdateRequest;
use App\Jobs\CreateMenuCard;
use App\Jobs\DeleteMenuCard;
use App\Jobs\ListMenuCards;
use App\Jobs\ShowMenuCard;
use App\Jobs\UpdateMenuCard;
use App\Models\MenuCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuCardController extends Controller
{
    public function index(Request $request)
    {
        ListMenuCards::dispatch();

        return Inertia::render('menucard.index', [
            'menucards' => $menucards,
        ]);
    }

    public function store(MenuCardStoreRequest $request): RedirectResponse
    {
        CreateMenuCard::dispatch($request);

        return redirect()->route('menucard.index');
    }

    public function show(Request $request, MenuCard $menuCard)
    {
        ShowMenuCard::dispatch($id);

        return Inertia::render('menucard.show', [
            'menucard' => $menucard,
        ]);
    }

    public function update(MenuCardUpdateRequest $request, MenuCard $menuCard): RedirectResponse
    {
        UpdateMenuCard::dispatch($request, $id);

        return redirect()->route('menucard.show', ['menucard' => $menucard]);
    }

    public function destroy(Request $request, MenuCard $menuCard): RedirectResponse
    {
        DeleteMenuCard::dispatch($id);

        return redirect()->route('menucard.index');
    }
}
