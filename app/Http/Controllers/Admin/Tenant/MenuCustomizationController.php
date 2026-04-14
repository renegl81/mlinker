<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuCustomizationController extends Controller
{
    public function show(Menu $menu): Response
    {
        $menu->load([
            'location',
            'template',
            'sections' => fn ($q) => $q->orderBy('sort_order'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
            'sections.products.allergens',
            'sections.products.ingredients',
        ]);

        $tenant = tenant();
        $domain = $tenant?->domains()->first()?->domain;
        $appUrl = config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $port = parse_url($appUrl, PHP_URL_PORT);
        $portSuffix = $port ? ":{$port}" : '';
        $publicMenuUrl = $domain ? "{$scheme}://{$domain}{$portSuffix}/menu/{$menu->id}" : null;

        return Inertia::render('admin/tenant/menus/Customize', [
            'menu' => $menu,
            'publicMenuUrl' => $publicMenuUrl,
            'customization' => $menu->customization ?? [],
            'templates' => Template::where('is_active', true)->get(['id', 'name', 'component_name']),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'customization' => ['nullable', 'array'],
            'customization.colors' => ['nullable', 'array'],
            'customization.colors.accent' => ['nullable', 'string', 'max:100'],
            'customization.colors.bg' => ['nullable', 'string', 'max:100'],
            'customization.colors.ink' => ['nullable', 'string', 'max:100'],
            'customization.colors.ink_soft' => ['nullable', 'string', 'max:100'],
            'customization.colors.rule' => ['nullable', 'string', 'max:100'],
            'customization.fonts' => ['nullable', 'array'],
            'customization.fonts.display' => ['nullable', 'string', 'max:100'],
            'customization.fonts.body' => ['nullable', 'string', 'max:100'],
            'customization.layout' => ['nullable', 'array'],
            'customization.layout.show_allergens' => ['nullable', 'boolean'],
            'customization.layout.show_ingredients' => ['nullable', 'boolean'],
            'customization.layout.show_product_images' => ['nullable', 'boolean'],
            'customization.layout.show_section_descriptions' => ['nullable', 'boolean'],
            'customization.layout.image_position' => ['nullable', 'string', 'in:left,right,top,hidden'],
            'customization.layout.price_alignment' => ['nullable', 'string', 'in:right,inline'],
            'customization.spacing' => ['nullable', 'array'],
            'customization.spacing.density' => ['nullable', 'string', 'in:compact,comfortable,spacious'],
            'customization.header' => ['nullable', 'array'],
            'customization.header.show_restaurant_name' => ['nullable', 'boolean'],
            'customization.header.tagline' => ['nullable', 'string', 'max:255'],
            'customization.header.name_display_style' => ['nullable', 'string', 'in:default,uppercase,italic'],
            'customization.sections' => ['nullable', 'array'],
            'customization.sections.divider_style' => ['nullable', 'string', 'in:line,ornament,none'],
            'customization.sections.title_alignment' => ['nullable', 'string', 'in:left,center'],
            'customization.sections.numbering' => ['nullable', 'string', 'in:none,roman,arabic'],
        ]);

        // Filter customization by plan features
        $plan = $this->currentPlan();
        $customization = $validated['customization'] ?? [];
        $filtered = $this->filterByPlan($customization, $plan);

        // Remove empty/null nested arrays to keep JSON clean
        $filtered = array_filter($filtered, fn ($v) => is_array($v) ? ! empty(array_filter($v, fn ($x) => $x !== null)) : $v !== null);

        $menu->update(['customization' => ! empty($filtered) ? $filtered : null]);

        return back()->with('success', 'Personalización guardada.');
    }

    public function reset(Menu $menu)
    {
        $menu->update(['customization' => null]);

        return back()->with('success', 'Personalización restablecida.');
    }

    private function currentPlan(): ?Plan
    {
        $subscription = Subscription::where('tenant_id', tenant()->id)
            ->latest()
            ->with('plan')
            ->first();

        return $subscription?->plan ?? Plan::free();
    }

    private function filterByPlan(array $customization, ?Plan $plan): array
    {
        $filtered = [];

        if ($plan?->has_menu_colors && isset($customization['colors'])) {
            $filtered['colors'] = $customization['colors'];
        }

        if ($plan?->has_menu_fonts && isset($customization['fonts'])) {
            $filtered['fonts'] = $customization['fonts'];
        }

        if ($plan?->has_menu_layout && isset($customization['layout'])) {
            $filtered['layout'] = $customization['layout'];
        }

        if ($plan?->has_menu_advanced_style) {
            if (isset($customization['spacing'])) {
                $filtered['spacing'] = $customization['spacing'];
            }
            if (isset($customization['header'])) {
                $filtered['header'] = $customization['header'];
            }
            if (isset($customization['sections'])) {
                $filtered['sections'] = $customization['sections'];
            }
        }

        return $filtered;
    }
}
