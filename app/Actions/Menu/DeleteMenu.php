<?php

namespace App\Actions\Menu;

use App\Models\Menu;

class DeleteMenu
{
    public function execute(Menu $menu): bool
    {
        return $menu->delete();
    }
}
