<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationStoreRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Jobs\CreateLocation;
use App\Jobs\DeleteLocation;
use App\Jobs\ListLocations;
use App\Jobs\ShowLocation;
use App\Jobs\UpdateLocation;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;


class LocationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $locations = ListLocations::dispatch($user);

        return Inertia::render('location.index', [
            'locations' => $locations,
        ]);
    }

    public function store(LocationStoreRequest $request): RedirectResponse
    {
        CreateLocation::dispatch($request);

        return redirect()->route('location.index');
    }

    public function show(Request $request, Location $location)
    {
        ShowLocation::dispatch($id);

        return Inertia::render('location.show', [
            'location' => $location,
        ]);
    }

    public function update(LocationUpdateRequest $request, Location $location): RedirectResponse
    {
        UpdateLocation::dispatch($request, $id);

        return redirect()->route('location.show', ['location' => $location]);
    }

    public function destroy(Request $request, Location $location): RedirectResponse
    {
        DeleteLocation::dispatch($id);

        return redirect()->route('location.index');
    }
}
