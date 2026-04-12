<?php

namespace App\Actions\Location;

use App\Models\Location;
use App\Models\OpeningHour;
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
            'is_pet_friendly' => $data['is_pet_friendly'] ?? $location->is_pet_friendly,
            'has_wifi' => $data['has_wifi'] ?? $location->has_wifi,
            'has_terrace' => $data['has_terrace'] ?? $location->has_terrace,
            'has_parking' => $data['has_parking'] ?? $location->has_parking,
            'is_accessible' => $data['is_accessible'] ?? $location->is_accessible,
            'reservation_url' => array_key_exists('reservation_url', $data) ? $data['reservation_url'] : $location->reservation_url,
            'reservation_phone' => array_key_exists('reservation_phone', $data) ? $data['reservation_phone'] : $location->reservation_phone,
            'instagram' => array_key_exists('instagram', $data) ? $data['instagram'] : $location->instagram,
            'facebook' => array_key_exists('facebook', $data) ? $data['facebook'] : $location->facebook,
            'google_maps_url' => array_key_exists('google_maps_url', $data) ? $data['google_maps_url'] : $location->google_maps_url,
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

        if (isset($data['opening_hours']) && is_array($data['opening_hours'])) {
            foreach ($data['opening_hours'] as $hour) {
                OpeningHour::updateOrCreate(
                    ['location_id' => $location->id, 'weekday' => $hour['weekday']],
                    [
                        'opens_at' => ($hour['is_closed'] ?? false) ? null : ($hour['opens_at'] ?? '09:00'),
                        'closes_at' => ($hour['is_closed'] ?? false) ? null : ($hour['closes_at'] ?? '22:00'),
                        'is_closed' => $hour['is_closed'] ?? false,
                    ]
                );
            }
        }

        return $location->fresh();
    }
}
