<?php

namespace App\Filament\Widgets;

use App\Models\Menu;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Stancl\Tenancy\Database\TenantScope;

class PlatformStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTenants = Tenant::count();

        $activeTenants = Subscription::whereIn('stripe_status', ['active', 'free', 'trialing'])
            ->distinct('tenant_id')
            ->count('tenant_id');

        $activeSubscriptions = Subscription::whereIn('stripe_status', ['active', 'free', 'trialing'])->count();

        $mrr = Subscription::where('stripe_status', 'active')
            ->whereNull('trial_ends_at')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        $totalUsers = User::count();

        $publishedMenus = Menu::withoutGlobalScope(TenantScope::class)
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Tenants totales', $totalTenants)
                ->description('Tenants registrados en la plataforma')
                ->color('primary'),

            Stat::make('Tenants activos', $activeTenants)
                ->description('Con subscripción activa o gratuita')
                ->color('success'),

            Stat::make('Subscripciones activas', $activeSubscriptions)
                ->description('Active, free y trialing')
                ->color('info'),

            Stat::make('MRR estimado', '€'.number_format((float) $mrr, 2))
                ->description('Suma de planes activos sin trial')
                ->color('warning'),

            Stat::make('Usuarios totales', $totalUsers)
                ->description('Usuarios registrados')
                ->color('primary'),

            Stat::make('Menús publicados', $publishedMenus)
                ->description('Menús con is_active=true cross-tenant')
                ->color('success'),
        ];
    }
}
