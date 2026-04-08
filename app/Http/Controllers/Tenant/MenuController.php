<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MenuController extends Controller
{
    public function __invoke(Menu $menu): Response
    {
        if (! $menu->is_active) {
            throw new NotFoundHttpException;
        }

        $menu->load([
            'location',
            'template',
            'sections' => fn ($q) => $q->orderBy('id'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
            'sections.products.allergens',
            'sections.products.ingredients',
        ]);

        $template = $menu->template?->component_name ?? 'Basic';

        return Inertia::render('tenant/templates/'.$template, [
            'menu' => $menu,
        ]);
    }
}
