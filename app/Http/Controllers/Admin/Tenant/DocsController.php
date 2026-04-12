<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocsController extends Controller
{
    /**
     * @return array<int, array{slug: string, icon: string, sections: array<int, array{heading: string, body: string, tip?: string, list?: array<int, string>}>}>
     */
    private function topics(): array
    {
        return [
            [
                'slug' => 'getting-started',
                'icon' => 'Rocket',
                'sections' => [
                    ['heading' => 'docs.getting_started.intro_title', 'body' => 'docs.getting_started.intro_body'],
                    ['heading' => 'docs.getting_started.step1_title', 'body' => 'docs.getting_started.step1_body'],
                    ['heading' => 'docs.getting_started.step2_title', 'body' => 'docs.getting_started.step2_body'],
                    ['heading' => 'docs.getting_started.step3_title', 'body' => 'docs.getting_started.step3_body'],
                    ['heading' => 'docs.getting_started.step4_title', 'body' => 'docs.getting_started.step4_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.getting_started.tip'],
                ],
            ],
            [
                'slug' => 'locations',
                'icon' => 'Building2',
                'sections' => [
                    ['heading' => 'docs.locations.what_title', 'body' => 'docs.locations.what_body'],
                    ['heading' => 'docs.locations.create_title', 'body' => 'docs.locations.create_body'],
                    ['heading' => 'docs.locations.fields_title', 'body' => 'docs.locations.fields_body'],
                    ['heading' => 'docs.locations.multi_title', 'body' => 'docs.locations.multi_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.locations.tip'],
                ],
            ],
            [
                'slug' => 'menus',
                'icon' => 'BookOpen',
                'sections' => [
                    ['heading' => 'docs.menus.what_title', 'body' => 'docs.menus.what_body'],
                    ['heading' => 'docs.menus.create_title', 'body' => 'docs.menus.create_body'],
                    ['heading' => 'docs.menus.sections_title', 'body' => 'docs.menus.sections_body'],
                    ['heading' => 'docs.menus.reorder_title', 'body' => 'docs.menus.reorder_body'],
                    ['heading' => 'docs.menus.activate_title', 'body' => 'docs.menus.activate_body'],
                    ['heading' => 'docs.menus.templates_title', 'body' => 'docs.menus.templates_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.menus.tip'],
                ],
            ],
            [
                'slug' => 'products',
                'icon' => 'Utensils',
                'sections' => [
                    ['heading' => 'docs.products.add_title', 'body' => 'docs.products.add_body'],
                    ['heading' => 'docs.products.fields_title', 'body' => 'docs.products.fields_body'],
                    ['heading' => 'docs.products.allergens_title', 'body' => 'docs.products.allergens_body'],
                    ['heading' => 'docs.products.ingredients_title', 'body' => 'docs.products.ingredients_body'],
                    ['heading' => 'docs.products.tags_title', 'body' => 'docs.products.tags_body'],
                    ['heading' => 'docs.products.catalog_title', 'body' => 'docs.products.catalog_body'],
                    ['heading' => 'docs.products.merge_title', 'body' => 'docs.products.merge_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.products.tip'],
                ],
            ],
            [
                'slug' => 'translations',
                'icon' => 'Languages',
                'sections' => [
                    ['heading' => 'docs.translations.how_title', 'body' => 'docs.translations.how_body'],
                    ['heading' => 'docs.translations.auto_title', 'body' => 'docs.translations.auto_body'],
                    ['heading' => 'docs.translations.add_title', 'body' => 'docs.translations.add_body'],
                    ['heading' => 'docs.translations.remove_title', 'body' => 'docs.translations.remove_body'],
                    ['heading' => 'docs.translations.selector_title', 'body' => 'docs.translations.selector_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.translations.tip'],
                ],
            ],
            [
                'slug' => 'qr-codes',
                'icon' => 'QrCode',
                'sections' => [
                    ['heading' => 'docs.qr_codes.generate_title', 'body' => 'docs.qr_codes.generate_body'],
                    ['heading' => 'docs.qr_codes.customize_title', 'body' => 'docs.qr_codes.customize_body'],
                    ['heading' => 'docs.qr_codes.download_title', 'body' => 'docs.qr_codes.download_body'],
                    ['heading' => 'docs.qr_codes.placement_title', 'body' => 'docs.qr_codes.placement_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.qr_codes.tip'],
                ],
            ],
            [
                'slug' => 'team',
                'icon' => 'Users',
                'sections' => [
                    ['heading' => 'docs.team.roles_title', 'body' => 'docs.team.roles_body'],
                    ['heading' => 'docs.team.editor_title', 'body' => 'docs.team.editor_body'],
                    ['heading' => 'docs.team.scope_title', 'body' => 'docs.team.scope_body'],
                    ['heading' => 'docs.team.manage_title', 'body' => 'docs.team.manage_body'],
                    ['heading' => 'docs.team.plan_title', 'body' => 'docs.team.plan_body'],
                ],
            ],
            [
                'slug' => 'analytics',
                'icon' => 'BarChart2',
                'sections' => [
                    ['heading' => 'docs.analytics.what_title', 'body' => 'docs.analytics.what_body'],
                    ['heading' => 'docs.analytics.metrics_title', 'body' => 'docs.analytics.metrics_body'],
                    ['heading' => 'docs.analytics.sources_title', 'body' => 'docs.analytics.sources_body'],
                    ['heading' => 'docs.analytics.hours_title', 'body' => 'docs.analytics.hours_body'],
                    ['heading' => 'docs.analytics.devices_title', 'body' => 'docs.analytics.devices_body'],
                    ['heading' => 'docs.analytics.plan_title', 'body' => 'docs.analytics.plan_body'],
                    ['heading' => '', 'body' => '', 'tip' => 'docs.analytics.tip'],
                ],
            ],
            [
                'slug' => 'billing',
                'icon' => 'CreditCard',
                'sections' => [
                    ['heading' => 'docs.billing.plans_title', 'body' => 'docs.billing.plans_body'],
                    ['heading' => 'docs.billing.change_title', 'body' => 'docs.billing.change_body'],
                    ['heading' => 'docs.billing.trial_title', 'body' => 'docs.billing.trial_body'],
                    ['heading' => 'docs.billing.cancel_title', 'body' => 'docs.billing.cancel_body'],
                    ['heading' => 'docs.billing.stripe_title', 'body' => 'docs.billing.stripe_body'],
                ],
            ],
            [
                'slug' => 'website',
                'icon' => 'Globe',
                'sections' => [
                    ['heading' => 'docs.website.what_title', 'body' => 'docs.website.what_body'],
                    ['heading' => 'docs.website.activate_title', 'body' => 'docs.website.activate_body'],
                    ['heading' => 'docs.website.template_title', 'body' => 'docs.website.template_body'],
                    ['heading' => 'docs.website.content_title', 'body' => 'docs.website.content_body'],
                    ['heading' => 'docs.website.branding_title', 'body' => 'docs.website.branding_body'],
                ],
            ],
        ];
    }

    public function index(): Response
    {
        return Inertia::render('admin/tenant/docs/Index', [
            'topics' => $this->topics(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $topics = $this->topics();
        $topic = collect($topics)->firstWhere('slug', $slug);

        if ($topic === null) {
            throw new NotFoundHttpException;
        }

        return Inertia::render('admin/tenant/docs/Show', [
            'topic' => $topic,
            'topics' => $topics,
        ]);
    }
}
