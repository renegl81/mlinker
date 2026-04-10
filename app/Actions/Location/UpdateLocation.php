<?php

namespace App\Actions\Location;

use App\Models\Location;
use App\Support\ImageHelper;
use Illuminate\Support\Facades\Storage;

class UpdateLocation
{
    public function execute(Location $location, array $data): Location
    {
        $updateData = [
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
            'lang' => $data['lang'] ?? 'es',
            'languages' => $data['languages'] ?? $location->languages ?? ['es'],
            'social_medias' => $data['social_medias'] ?? $location->social_medias ?? [],
            'latitude' => $data['latitude'] ?? $location->latitude,
            'longitude' => $data['longitude'] ?? $location->longitude,
            'primary_color' => $data['primary_color'] ?? $location->primary_color,
            'secondary_color' => $data['secondary_color'] ?? $location->secondary_color,
        ];

        if (array_key_exists('image_url', $data)) {
            $incoming = $data['image_url'];
            if (is_string($incoming) && str_starts_with($incoming, 'data:image')) {
                if ($location->image_url && ! str_starts_with($location->image_url, 'http')) {
                    Storage::disk('public')->delete($location->image_url);
                }
                $updateData['image_url'] = ImageHelper::storeBase64Image($incoming, 'locations');
            } elseif ($incoming === null) {
                if ($location->image_url && ! str_starts_with($location->image_url, 'http')) {
                    Storage::disk('public')->delete($location->image_url);
                }
                $updateData['image_url'] = null;
            }
        }

        $location->update($updateData);

        return $location->fresh();
    }
}
