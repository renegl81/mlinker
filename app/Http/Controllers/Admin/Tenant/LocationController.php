<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Location\CreateLocation;
use App\Actions\Location\DeleteLocation;
use App\Actions\Location\DuplicateLocation;
use App\Actions\Location\GetLocations;
use App\Actions\Location\UpdateLocation;
use App\Actions\Plan\CheckLimit;
use App\Exceptions\PlanLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationStoreRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Models\Country;
use App\Models\Location;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index(Request $request, GetLocations $listLocations)
    {
        $locations = $listLocations->execute($request);

        return Inertia::render('admin/tenant/locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/tenant/locations/Create', [
            'countries' => Country::all(['id', 'name']),
        ]);
    }

    public function store(LocationStoreRequest $request, CreateLocation $createLocation): RedirectResponse
    {
        try {
            (new CheckLimit)->execute('locations', throw: true);
            $data = $request->validated();
            $createLocation->execute($data);

            return redirect()->route('tenant.locations.index')
                ->with('success', 'Local creado correctamente.');

        } catch (PlanLimitExceededException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }

    public function edit(Location $location)
    {
        return Inertia::render('admin/tenant/locations/Edit', [
            'location' => $location->load(['openingHours', 'images']),
            'countries' => Country::all(['id', 'name']),
        ]);
    }

    public function show(Location $location)
    {
        $tenant = tenant();
        $domain = $tenant?->domains()->first()?->domain;
        $appUrl = config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $port = parse_url($appUrl, PHP_URL_PORT);
        $portSuffix = $port ? ":{$port}" : '';
        $publicUrl = $domain ? "{$scheme}://{$domain}{$portSuffix}/" : null;

        return Inertia::render('admin/tenant/locations/Show', [
            'location' => $location->load(['country', 'menus', 'openingHours', 'images']),
            'publicUrl' => $publicUrl,
        ]);
    }

    public function update(LocationUpdateRequest $request, UpdateLocation $updateLocation, Location $location): RedirectResponse
    {
        try {
            $data = $request->validated();
            $updateLocation->execute($location, $data);
        } catch (Exception $e) {
            return redirect()->route('tenant.locations.edit', ['location' => $location])
                ->withInput()
                ->with('error', 'Ocurrió un error: '.$e->getMessage());
        }

        return redirect()->route('tenant.locations.show', [
            'location' => $location,
        ]);
    }

    public function duplicate(Location $location, DuplicateLocation $action): RedirectResponse
    {
        $plan = tenant()?->subscription?->plan;
        if (! $plan || $plan->slug === 'free') {
            return back()->with('error', 'La duplicación de locales requiere un plan de pago.');
        }

        try {
            $newLocation = $action->execute($location);

            return redirect()->route('tenant.locations.show', $newLocation)
                ->with('success', 'Local duplicado correctamente.');
        } catch (Exception $e) {
            Log::error('Location duplication failed', [
                'location_id' => $location->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al duplicar el local.');
        }
    }

    public function destroy(DeleteLocation $deleteLocation, Location $location): RedirectResponse
    {
        $deleteLocation->execute($location);

        return redirect()->route('tenant.locations.index');
    }
}
