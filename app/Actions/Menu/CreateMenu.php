<?php

namespace App\Actions\Menu;

use App\Models\Menu;

class CreateMenu
{
    public function __invoke(array $data): Menu
    {
        return Menu::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
