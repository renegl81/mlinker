<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpeningHourStoreRequest;
use App\Http\Requests\OpeningHourUpdateRequest;
use App\Jobs\CreateOpeningHour;
use App\Jobs\DeleteOpeningHour;
use App\Jobs\ListOpeningHours;
use App\Jobs\ShowOpeningHour;
use App\Jobs\UpdateOpeningHour;
use App\Models\OpeningHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OpeningHourController extends Controller
{
    public function index(Request $request)
    {
        ListOpeningHours::dispatch();

        return Inertia::render('openinghour.index', [
            'openinghours' => $openinghours,
        ]);
    }

    public function store(OpeningHourStoreRequest $request): RedirectResponse
    {
        CreateOpeningHour::dispatch($request);

        return redirect()->route('openinghour.index');
    }

    public function show(Request $request, OpeningHour $openingHour)
    {
        ShowOpeningHour::dispatch($id);

        return Inertia::render('openinghour.show', [
            'openinghour' => $openinghour,
        ]);
    }

    public function update(OpeningHourUpdateRequest $request, OpeningHour $openingHour): RedirectResponse
    {
        UpdateOpeningHour::dispatch($request, $id);

        return redirect()->route('openinghour.show', ['openinghour' => $openinghour]);
    }

    public function destroy(Request $request, OpeningHour $openingHour): RedirectResponse
    {
        DeleteOpeningHour::dispatch($id);

        return redirect()->route('openinghour.index');
    }
}
