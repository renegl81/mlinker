<?php

namespace App\Actions\Location;

use App\Http\Requests\LocationUpdateRequest;
use App\Models\Location;

class UpdateLocation
{
    public function execute(Location $location, LocationUpdateRequest $request): Location
    {
        $location->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'currency' => $request->input('currency', 'EUR'),
            'time_zone' => $request->input('time_zone'),
            'postal_code' => $request->input('postal_code'),
            'country_id' => $request->input('country_id'),
            'user_id' => $request->input('user_id'),
            'logo_url' => $request->input('logo_url'),
            'image_url' => $request->input('image_url'),
            'lang' => $request->input('lang', 'es'),
            'languages' => $request->input('languages', '{}'),
            'social_medias' => $request->input('social_medias', '{}'),
            'latitud' => $request->input('latitude'),
            'longitud' => $request->input('longitude'),
        ]);

        return $location->fresh();
    }
}
