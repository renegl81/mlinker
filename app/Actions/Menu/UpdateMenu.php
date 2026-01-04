<?php

namespace App\Actions\Menu;

use App\Models\Menu;

class UpdateMenu
{
    public function execute(Menu $menu, array $data): Menu
    {
        $menu->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $menu->is_active,
        ]);

        return $menu->fresh();
    }
}
