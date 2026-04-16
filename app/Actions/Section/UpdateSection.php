<?php

declare(strict_types=1);

namespace App\Actions\Section;

use App\Models\Section;

class UpdateSection
{
    public function execute(Section $section, array $data): Section
    {
        $section->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $section->fresh();
    }
}
