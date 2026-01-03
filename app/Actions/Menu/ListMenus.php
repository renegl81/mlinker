<?php

namespace App\Actions\Menu;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListMenus
{
    public function __invoke(Request $request): LengthAwarePaginator
    {
        $query = Menu::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        return $query->latest()->paginate(15);
    }
}
