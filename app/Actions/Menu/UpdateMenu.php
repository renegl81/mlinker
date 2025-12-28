<?php

namespace App\Actions\Menu;

use App\Models\Menu;

class UpdateMenu
{
    public function __invoke(Menu $menu, array $data): Menu
    {
        $menu->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $menu->is_active,
        ]);

        return $menu->fresh();
    }
}
