<?php

declare(strict_types=1);

namespace App\Actions\Section;

use App\Models\Section;

class PatchSection
{
    public function execute(Section $section, array $data): Section
    {
        $allowed = ['name', 'description'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (! empty($updateData)) {
            $section->update($updateData);
        }

        return $section->fresh();
    }
}
