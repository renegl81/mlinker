<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Location\CreateLocation;
use App\Actions\Location\DeleteLocation;
use App\Actions\Location\GetLocations;
use App\Actions\Location\UpdateLocation;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationStoreRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index(Request $request, GetLocations $listLocations)
    {
        $locations = $listLocations->execute($request);

        return Inertia::render('admin/tenant/Locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/tenant/Locations/Create');
    }

    public function store(LocationStoreRequest $request, CreateLocation $createLocation): RedirectResponse
    {
        $createLocation->execute($request);

        return redirect()->route('tenant.locations.index');
    }

    public function show(Request $request, Location $location)
    {
        return Inertia::render('Admin/Tenant/Locations/Show', [
            'location' => $location,
        ]);
    }

    public function update(LocationUpdateRequest $request, UpdateLocation $updateLocation, Location $location): RedirectResponse
    {
        $updateLocation->execute($location, $request);

        return redirect()->route('tenant.locations.show', [
            'location' => $location
        ]);
    }

    public function destroy(DeleteLocation $deleteLocation, Location $location): RedirectResponse
    {
        $deleteLocation->execute($location);

        return redirect()->route('tenant.locations.index');
    }
}
