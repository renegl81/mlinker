<?php

declare(strict_types=1);

namespace App\Actions\Section;

use App\Models\Menu;
use App\Models\Section;

class CreateSection
{
    public function execute(Menu $menu, array $data): Section
    {
        $maxSort = Section::where('menu_id', $menu->id)->max('sort_order') ?? 0;

        return Section::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'menu_id' => $menu->id,
            'tenant_id' => tenant('id'),
            'sort_order' => $maxSort + 1,
        ]);
    }
}
