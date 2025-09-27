<?php

namespace App\Http\Controllers;

use App\Http\Requests\TranslationStoreRequest;
use App\Http\Requests\TranslationUpdateRequest;
use App\Jobs\CreateTranslation;
use App\Jobs\DeleteTranslation;
use App\Jobs\ListTranslations;
use App\Jobs\ShowTranslation;
use App\Jobs\UpdateTranslation;
use App\Models\Translation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        ListTranslations::dispatch();

        return Inertia::render('translation.index', [
            'translations' => $translations,
        ]);
    }

    public function store(TranslationStoreRequest $request): RedirectResponse
    {
        CreateTranslation::dispatch($request);

        return redirect()->route('translation.index');
    }

    public function show(Request $request, Translation $translation)
    {
        ShowTranslation::dispatch($id);

        return Inertia::render('translation.show', [
            'translation' => $translation,
        ]);
    }

    public function update(TranslationUpdateRequest $request, Translation $translation): RedirectResponse
    {
        UpdateTranslation::dispatch($request, $id);

        return redirect()->route('translation.show', ['translation' => $translation]);
    }

    public function destroy(Request $request, Translation $translation): RedirectResponse
    {
        DeleteTranslation::dispatch($id);

        return redirect()->route('translation.index');
    }
}
