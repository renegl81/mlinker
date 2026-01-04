<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __invoke(Menu $menu): Response
    {
        return Inertia::render('tenant/templates/'.$menu->template->component_name, [
            'menu' => $menu->load(['location', 'template', 'sections']),
        ]);

    }
}
