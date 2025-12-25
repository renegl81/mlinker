<?php

namespace App\Actions\User;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GetTenantUsers
{
    public function execute(Request $request, $paginate =  true): Collection | LengthAwarePaginator
    {
        if($paginate) {
            return tenant()->users()
                ->paginate(10)
                ->withQueryString();
        }
        return tenant()->users;
    }
}
