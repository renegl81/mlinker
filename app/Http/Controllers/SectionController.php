<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionStoreRequest;
use App\Http\Requests\SectionUpdateRequest;
use App\Jobs\CreateSection;
use App\Jobs\DeleteSection;
use App\Jobs\ListSections;
use App\Jobs\ShowSection;
use App\Jobs\UpdateSection;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        ListSections::dispatch();

        return Inertia::render('section.index', [
            'sections' => $sections,
        ]);
    }

    public function store(SectionStoreRequest $request): RedirectResponse
    {
        CreateSection::dispatch($request);

        return redirect()->route('section.index');
    }

    public function show(Request $request, Section $section)
    {
        ShowSection::dispatch($id);

        return Inertia::render('section.show', [
            'section' => $section,
        ]);
    }

    public function update(SectionUpdateRequest $request, Section $section): RedirectResponse
    {
        UpdateSection::dispatch($request, $id);

        return redirect()->route('section.show', ['section' => $section]);
    }

    public function destroy(Request $request, Section $section): RedirectResponse
    {
        DeleteSection::dispatch($id);

        return redirect()->route('section.index');
    }
}
