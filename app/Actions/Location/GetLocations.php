<?php

namespace App\Actions\Location;

use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetLocations
{
    public function execute(Request $request, $paginate =  true): Collection | LengthAwarePaginator
    {
        if($paginate) {
            return Location::query()
                ->applyFilters($request)
                ->latest()
                ->paginate(15);
        }
        Location::query()
            ->applyFilters($request)
            ->latest();
    }
}
