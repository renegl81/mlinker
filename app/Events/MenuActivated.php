<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Menu;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Menu $menu,
    ) {}
}
