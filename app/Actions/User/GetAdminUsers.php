<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GetAdminUsers
{
    public function execute(Request $request, $paginate = true): Collection|LengthAwarePaginator
    {
        $query = User::query();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($paginate) {
            return $query->paginate(10)
                ->withQueryString();
        }

        return $query->get();
    }
}
