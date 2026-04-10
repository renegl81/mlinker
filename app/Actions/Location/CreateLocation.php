<?php

namespace App\Actions\Location;

use App\Models\Location;
use App\Support\ImageHelper;
use Illuminate\Support\Str;

class CreateLocation
{
    public function execute(array $data): Location
    {
        $tenant = tenant();
        $slug = Str::slug($data['name']);

        $imageUrl = null;
        if (isset($data['image_url']) && is_string($data['image_url']) && str_starts_with($data['image_url'], 'data:image')) {
            $imageUrl = ImageHelper::storeBase64Image($data['image_url'], 'locations');
        }

        return Location::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'country_id' => $data['country_id'],
            'province' => $data['province'],
            'currency' => $data['currency'] ?? 'EUR',
            'time_zone' => $data['time_zone'] ?? 'Europe/Madrid',
            'time_format' => $data['time_format'] ?? 'H:i',
            'postal_code' => $data['postal_code'],
            'lang' => $data['lang'] ?? 'es',
            'languages' => $data['languages'] ?? ['es'],
            'social_medias' => $data['social_medias'] ?? [],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'image_url' => $imageUrl,
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'slug' => $slug,
            'url' => $tenant->url.'/'.$slug,
        ]);
    }
}
