<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GetAdminUsers
{
    public function execute(Request $request, $paginate =  true): Collection | LengthAwarePaginator
    {
        if($paginate) {
            return User::paginate(10)
                ->withQueryString();
        }
        return User::all();
    }
}
