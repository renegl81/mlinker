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
            'sections' => fn ($q) => $q->orderBy('sort_order'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
            'sections.products.allergens',
            'sections.products.ingredients',
        ]);

        $plan = tenant()?->subscription?->plan;
        $showBranding = $plan?->show_branding ?? true;
        $hasMultilang = $plan?->has_multilang ?? false;

        $sourceLocale = config('menulinker.source_locale', 'es');
        $supportedCodes = array_keys(config('menulinker.supported_locales', []));

        // Locales actually available for this menu = source + those present in menu.translations
        $availableLocales = [$sourceLocale];
        foreach (array_keys($menu->translations ?? []) as $code) {
            if ($code !== $sourceLocale && in_array($code, $supportedCodes, true) && ! in_array($code, $availableLocales, true)) {
                $availableLocales[] = $code;
            }
        }

        // Detect requested locale, restricted to what's actually available
        $requestedLocale = (string) request()->query('lang', $sourceLocale);
        $locale = in_array($requestedLocale, $availableLocales, true) ? $requestedLocale : $sourceLocale;

        if ($hasMultilang && $locale !== $sourceLocale) {
            $this->applyTranslations($menu, $locale);
        }

        $shortUrl = route('tenant_public.tenant_menu_show', ['menu' => $menu->id]);

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
            'locale' => $locale,
            'hasMultilang' => $hasMultilang,
            'availableLocales' => $availableLocales,
            'supportedLocales' => config('menulinker.supported_locales'),
        ]);
    }

    /**
     * Mutate the menu model (and nested relations) in place with translated values.
     */
    private function applyTranslations(Menu $menu, string $locale): void
    {
        $menu->name = $menu->getTranslated('name', $locale);
        $menu->description = $menu->getTranslated('description', $locale);

        foreach ($menu->sections as $section) {
            $section->name = $section->getTranslated('name', $locale);
            $section->description = $section->getTranslated('description', $locale);

            foreach ($section->products as $product) {
                $product->name = $product->getTranslated('name', $locale);
                $product->description = $product->getTranslated('description', $locale);

                foreach ($product->ingredients as $ingredient) {
                    $ingredient->name = $ingredient->getTranslated('name', $locale);
                }
            }
        }
    }
}
