<?php

namespace App\Actions\Location;

use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuplicateLocation
{
    public function execute(Location $location): Location
    {
        return DB::transaction(function () use ($location) {
            $location->load(['openingHours']);

            $newLocation = $location->replicate([
                'id', 'slug', 'url', 'created_at', 'updated_at',
            ]);
            $newLocation->name = $location->name.' (copia)';
            $newLocation->slug = Str::slug($newLocation->name).'-'.Str::random(4);
            $newLocation->url = $newLocation->slug;
            $newLocation->save();

            // Copy opening hours
            foreach ($location->openingHours as $hour) {
                $newHour = $hour->replicate(['id', 'created_at', 'updated_at']);
                $newHour->location_id = $newLocation->id;
                $newHour->save();
            }

            return $newLocation;
        });
    }
}
