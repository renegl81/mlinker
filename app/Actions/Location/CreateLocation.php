<?php

namespace App\Actions\Location;

use App\Http\Requests\LocationStoreRequest;
use App\Models\Location;

class CreateLocation
{
    public function execute(LocationStoreRequest $request): Location
    {
        return Location::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'currency' => $request->input('currency', 'EUR'),
            'time_zone' => $request->input('time_zone'),
            'postal_code' => $request->input('postal_code'),
            'lang' => $request->input('lang', 'es'),
            'languages' => $request->input('languages', '{}'),
            'social_medias' => $request->input('social_medias', '{}'),
            'latitud' => $request->input('latitude'),
            'longitud' => $request->input('longitude'),
        ]);
    }
}
