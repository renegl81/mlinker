<?php

namespace App\Actions\Location;

use App\Models\Location;

class UpdateLocation
{
    public function execute(Location $location, array $data): Location
    {
        $location->update([
            'name' => $data['name'] ?? $location->name,
            'description' => $data['description'] ?? $location->description,
            'address' => $data['address'] ?? $location->address,
            'phone' => $data['phone'] ?? $location->phone,
            'city' => $data['city'] ?? $location->city,
            'province' => $data['province'] ?? $location->province,
            'currency' => $data['currency'] ?? 'EUR',
            'time_zone' => $data['time_zone'] ?? $location->time_zone,
            'postal_code' => $data['postal_code'] ?? $location->postal_code,
            'country_id' => $data['country_id'] ?? $location->country_id,
            'logo_url' => $data['logo_url'] ?? $location->logo_url,
            'image_url' => $data['image_url'] ?? $location->image_url,
            'lang' => $data['lang'] ?? 'es',
            'languages' => $data['languages'] ?? $location->languages ?? ['es'],
            'social_medias' => $data['social_medias'] ?? $location->social_medias ?? [],
            'latitude' => $data['latitude'] ?? $data['latitude'] ?? $location->latitude,
            'longitude' => $data['longitude'] ?? $data['longitude'] ?? $location->longitude,
        ]);

        return $location->fresh();
    }
}
