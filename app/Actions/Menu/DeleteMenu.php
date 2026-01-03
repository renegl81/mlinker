<?php

namespace App\Actions\Menu;

use App\Models\Menu;

class DeleteMenu
{
    public function __invoke(Menu $menu): bool
    {
        return $menu->delete();
    }
}
