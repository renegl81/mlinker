<?php

namespace App\Actions\Analytics;

use App\Models\MenuView;
use App\Models\PageView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetTenantOverview
{
    /**
     * @return array{
     *   total_views: int,
     *   views_by_day: array<int, array{date: string, count: int}>,
     *   top_menus: array<int, array{menu_id: int, name: string, count: int}>,
     *   views_by_source: array<string, int>,
     *   current_period: array{start: string, end: string, days: int},
     *   home_views: int,
     *   qr_downloads: int,
     *   views_by_hour: array<int, array{hour: int, count: int}>,
     *   views_by_device: array{mobile: int, tablet: int, desktop: int},
     *   weekly_comparison: array{current_week: int, previous_week: int, change_percent: float},
     * }
     */
    public function execute(string $tenantId, int $days = 30): array
    {
        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $baseQuery = MenuView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('viewed_at', '>=', $start)
            ->where('viewed_at', '<=', $end);

        $totalViews = (clone $baseQuery)->count();

        $viewsByDay = $this->buildViewsByDay((clone $baseQuery), $start, $days);

        $topMenus = $this->buildTopMenus($tenantId, $start, $end);

        $viewsBySource = $this->buildViewsBySource((clone $baseQuery));

        $homeViews = $this->buildHomeViews($tenantId, $start, $end);

        $qrDownloads = $this->buildQrDownloads($tenantId, $start, $end);

        $viewsByHour = $this->buildViewsByHour($tenantId, $start, $end);

        $viewsByDevice = $this->buildViewsByDevice($tenantId, $start, $end);

        $weeklyComparison = $this->buildWeeklyComparison($tenantId);

        return [
            'total_views' => $totalViews,
            'views_by_day' => $viewsByDay,
            'top_menus' => $topMenus,
            'views_by_source' => $viewsBySource,
            'current_period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'days' => $days,
            ],
            'home_views' => $homeViews,
            'qr_downloads' => $qrDownloads,
            'views_by_hour' => $viewsByHour,
            'views_by_device' => $viewsByDevice,
            'weekly_comparison' => $weeklyComparison,
        ];
    }

    /**
     * @return array<int, array{date: string, count: int}>
     */
    private function buildViewsByDay(mixed $baseQuery, Carbon $start, int $days): array
    {
        $rows = $baseQuery
            ->select(DB::raw('DATE(viewed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(viewed_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $result[] = [
                'date' => $date,
                'count' => (int) ($rows[$date]?->count ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{menu_id: int, name: string, count: int}>
     */
    private function buildTopMenus(string $tenantId, Carbon $start, Carbon $end): array
    {
        return MenuView::withoutGlobalScopes()
            ->select('menu_views.menu_id', 'menus.name', DB::raw('COUNT(*) as count'))
            ->join('menus', 'menus.id', '=', 'menu_views.menu_id')
            ->where('menu_views.tenant_id', $tenantId)
            ->where('menu_views.viewed_at', '>=', $start)
            ->where('menu_views.viewed_at', '<=', $end)
            ->groupBy('menu_views.menu_id', 'menus.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'menu_id' => (int) $row->menu_id,
                'name' => $row->name,
                'count' => (int) $row->count,
            ])
            ->toArray();
    }

    /**
     * @return array<string, int>
     */
    private function buildViewsBySource(mixed $baseQuery): array
    {
        $sources = [
            'QR' => 0,
            'WhatsApp' => 0,
            'Google' => 0,
            'Social' => 0,
            'Directo' => 0,
        ];

        $rows = $baseQuery->select('referer', DB::raw('COUNT(*) as count'))
            ->groupBy('referer')
            ->get();

        foreach ($rows as $row) {
            $referer = (string) ($row->referer ?? '');
            $count = (int) $row->count;
            $source = $this->classifyReferer($referer);
            $sources[$source] += $count;
        }

        return $sources;
    }

    private function classifyReferer(string $referer): string
    {
        if ($referer === '') {
            return 'QR';
        }

        $lower = mb_strtolower($referer);

        if (str_contains($lower, 'wa.me') || str_contains($lower, 'whatsapp')) {
            return 'WhatsApp';
        }

        if (str_contains($lower, 'google')) {
            return 'Google';
        }

        if (
            str_contains($lower, 'facebook') ||
            str_contains($lower, 'twitter') ||
            str_contains($lower, 'instagram')
        ) {
            return 'Social';
        }

        return 'Directo';
    }

    private function buildHomeViews(string $tenantId, Carbon $start, Carbon $end): int
    {
        return PageView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('page_type', 'home')
            ->where('viewed_at', '>=', $start)
            ->where('viewed_at', '<=', $end)
            ->count();
    }

    private function buildQrDownloads(string $tenantId, Carbon $start, Carbon $end): int
    {
        return PageView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('event', 'qr_download')
            ->where('viewed_at', '>=', $start)
            ->where('viewed_at', '<=', $end)
            ->count();
    }

    /**
     * @return array<int, array{hour: int, count: int}>
     */
    private function buildViewsByHour(string $tenantId, Carbon $start, Carbon $end): array
    {
        $rows = MenuView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('viewed_at', '>=', $start)
            ->where('viewed_at', '<=', $end)
            ->select(DB::raw('EXTRACT(HOUR FROM viewed_at)::int as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('EXTRACT(HOUR FROM viewed_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $result = [];
        for ($h = 0; $h < 24; $h++) {
            $result[] = [
                'hour' => $h,
                'count' => (int) ($rows[$h]?->count ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return array{mobile: int, tablet: int, desktop: int}
     */
    private function buildViewsByDevice(string $tenantId, Carbon $start, Carbon $end): array
    {
        $devices = ['mobile' => 0, 'tablet' => 0, 'desktop' => 0];

        $rows = PageView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('viewed_at', '>=', $start)
            ->where('viewed_at', '<=', $end)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get();

        foreach ($rows as $row) {
            $type = (string) ($row->device_type ?? 'desktop');
            if (array_key_exists($type, $devices)) {
                $devices[$type] = (int) $row->count;
            } else {
                $devices['desktop'] += (int) $row->count;
            }
        }

        return $devices;
    }

    /**
     * @return array{current_week: int, previous_week: int, change_percent: float}
     */
    private function buildWeeklyComparison(string $tenantId): array
    {
        $currentStart = Carbon::now()->startOfWeek();
        $currentEnd = Carbon::now()->endOfDay();
        $previousStart = Carbon::now()->subWeek()->startOfWeek();
        $previousEnd = Carbon::now()->subWeek()->endOfWeek();

        $currentWeek = MenuView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('viewed_at', '>=', $currentStart)
            ->where('viewed_at', '<=', $currentEnd)
            ->count();

        $previousWeek = MenuView::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('viewed_at', '>=', $previousStart)
            ->where('viewed_at', '<=', $previousEnd)
            ->count();

        $changePercent = 0.0;
        if ($previousWeek > 0) {
            $changePercent = round((($currentWeek - $previousWeek) / $previousWeek) * 100, 1);
        } elseif ($currentWeek > 0) {
            $changePercent = 100.0;
        }

        return [
            'current_week' => $currentWeek,
            'previous_week' => $previousWeek,
            'change_percent' => $changePercent,
        ];
    }
}
