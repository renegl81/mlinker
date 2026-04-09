<?php

namespace App\Http\Controllers\Admin\Core;

use App\Actions\Analytics\GetTenantOverview;
use App\Http\Controllers\Controller;
use App\Models\MenuView;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(GetTenantOverview $analytics): Response
    {
        $tenant = tenancy()->tenant;

        // Ruta global de superadmin (sin tenant inicializado)
        if (! $tenant) {
            return Inertia::render('Dashboard');
        }

        $subscription = $tenant->subscription()->with('plan')->first();
        $plan = $subscription?->plan;
        $hasAnalytics = (bool) ($plan?->has_analytics ?? false);
        $currentPlan = $plan?->name ?? 'Free';

        $tenantId = $tenant->id;

        if ($hasAnalytics) {
            $data = $analytics->execute($tenantId, 30);

            return Inertia::render('admin/tenant/Dashboard', array_merge($data, [
                'hasAnalytics' => true,
                'currentPlan' => $currentPlan,
            ]));
        }

        // Plan Free: solo total_views real + datos vacíos para el teaser
        $totalViews = MenuView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('viewed_at', '>=', Carbon::now()->subDays(30))
            ->count();

        return Inertia::render('admin/tenant/Dashboard', [
            'hasAnalytics' => false,
            'currentPlan' => $currentPlan,
            'total_views' => $totalViews,
            'views_by_day' => [],
            'top_menus' => [],
            'views_by_source' => [],
            'current_period' => [
                'start' => Carbon::now()->subDays(30)->toDateString(),
                'end' => Carbon::now()->toDateString(),
                'days' => 30,
            ],
        ]);
    }
}
