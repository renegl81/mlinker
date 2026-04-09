<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\TrackMenuView;
use App\Models\Menu;
use Illuminate\Support\Str;
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

        $plan = tenant()?->subscription?->plan;
        $showBranding = $plan?->show_branding ?? true;

        $shortUrl = route('tenant_public.tenant_menu_short', ['menu' => $menu->id]);

        $meta = [
            'title' => "{$menu->name} — {$menu->location->name}",
            'description' => Str::limit($menu->description ?? "Carta de {$menu->location->name}", 160),
            'image' => $menu->image_path,
            'url' => $shortUrl,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $menu->location->name,
            'address' => $menu->location->address,
            'telephone' => $menu->location->phone,
            'hasMenu' => [
                '@type' => 'Menu',
                'name' => $menu->name,
                'description' => $menu->description,
                'hasMenuSection' => $menu->sections->map(fn ($section) => [
                    '@type' => 'MenuSection',
                    'name' => $section->name,
                    'description' => $section->description,
                    'hasMenuItem' => $section->products->map(fn ($product) => [
                        '@type' => 'MenuItem',
                        'name' => $product->name,
                        'description' => $product->description,
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => $product->price,
                            'priceCurrency' => $menu->location->currency ?? 'EUR',
                        ],
                    ])->toArray(),
                ])->toArray(),
            ],
        ];

        $tenantSlug = tenant()?->id ?? '';
        $template = $menu->template?->component_name ?? 'Basic';

        if ($tenantSlug) {
            TrackMenuView::dispatch(
                $menu->id,
                $tenantSlug,
                request()->ip(),
                request()->userAgent(),
                request()->header('referer'),
            );
        }

        return Inertia::render('tenant/templates/'.$template, [
            'menu' => $menu,
            'showBranding' => $showBranding,
            'tenantSlug' => $tenantSlug,
            'meta' => $meta,
            'jsonLd' => $jsonLd,
            'shareUrl' => $shortUrl,
        ]);
    }
}
