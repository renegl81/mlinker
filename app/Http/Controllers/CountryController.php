<?php

namespace App\Http\Controllers;

use App\Http\Requests\CountryStoreRequest;
use App\Http\Requests\CountryUpdateRequest;
use App\Jobs\CreateCountry;
use App\Jobs\DeleteCountry;
use App\Jobs\ListCountries;
use App\Jobs\ShowCountry;
use App\Jobs\UpdateCountry;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        ListCountries::dispatch();

        return Inertia::render('country.index', [
            'countries' => $countries,
        ]);
    }

    public function store(CountryStoreRequest $request): RedirectResponse
    {
        CreateCountry::dispatch($request);

        return redirect()->route('country.index');
    }

    public function show(Request $request, Country $country)
    {
        ShowCountry::dispatch($id);

        return Inertia::render('country.show', [
            'country' => $country,
        ]);
    }

    public function update(CountryUpdateRequest $request, Country $country): RedirectResponse
    {
        UpdateCountry::dispatch($request, $id);

        return redirect()->route('country.show', ['country' => $country]);
    }

    public function destroy(Request $request, Country $country): RedirectResponse
    {
        DeleteCountry::dispatch($id);

        return redirect()->route('country.index');
    }
}
