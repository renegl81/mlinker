<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiLocationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $locations = Location::orderBy('id')->get();

        return LocationResource::collection($locations);
    }

    public function show(Location $location): LocationResource
    {
        $location->load(['menus' => fn ($q) => $q->where('is_active', true)->orderBy('id')]);

        return new LocationResource($location);
    }
}
