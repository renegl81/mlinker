<?php

declare(strict_types=1);

namespace App\Actions\Section;

use App\Models\Menu;
use App\Models\Section;

class ReorderSections
{
    /**
     * Reorder sections for a menu.
     *
     * @param  array<int>  $sectionIds  New ordered list of section IDs
     */
    public function execute(Menu $menu, array $sectionIds): void
    {
        foreach ($sectionIds as $index => $sectionId) {
            Section::where('id', $sectionId)
                ->where('menu_id', $menu->id)
                ->update(['sort_order' => $index + 1]);
        }
    }
}
