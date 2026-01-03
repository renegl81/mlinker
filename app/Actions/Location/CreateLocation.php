<?php

namespace App\Actions\Location;

use App\Http\Requests\LocationStoreRequest;
use App\Models\Location;
use Illuminate\Support\Str;

class CreateLocation
{
    public function execute(array $data): Location
    {
        $tenant = tenant();
        $slug = Str::slug($data['name']);
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
            'postal_code' => $data['postal_code'],
            'lang' => $data['lang'] ?? 'es',
            'languages' => $data['languages'] ?? ['es'],
            'social_medias' => $data['social_medias'] ?? [],
            'latitud' => $data['latitude'],
            'longitud' => $data['longitude'],
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'slug' => $slug,
            'url' =>  $tenant->url . '/' . $slug,
        ]);
    }
}
