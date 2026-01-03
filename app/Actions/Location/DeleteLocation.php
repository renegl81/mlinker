<?php

namespace App\Actions\Location;

use App\Models\Location;

class DeleteLocation
{
    public function execute(Location $location): bool
    {
        return $location->delete();
    }
}
