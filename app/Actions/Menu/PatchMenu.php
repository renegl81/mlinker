<?php

namespace App\Actions\Menu;

use App\Models\Menu;

class PatchMenu
{
    public function execute(Menu $menu, array $data): Menu
    {
        $allowed = ['name', 'description', 'is_active', 'template_id', 'show_prices', 'show_currency', 'show_calories', 'lang'];

        $updateData = array_intersect_key($data, array_flip($allowed));

        if (! empty($updateData)) {
            $menu->update($updateData);
        }

        return $menu->fresh();
    }
}
