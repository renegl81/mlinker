<?php

declare(strict_types=1);

namespace App\Actions\Section;

use App\Models\Section;
use Illuminate\Support\Facades\DB;

class DeleteSection
{
    public function execute(Section $section): void
    {
        // Detach all products from this section (pivot cleanup)
        DB::table('product_section')->where('section_id', $section->id)->delete();

        $section->delete();
    }
}
