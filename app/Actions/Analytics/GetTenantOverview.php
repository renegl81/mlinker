<?php

namespace App\Actions\Analytics;

use App\Models\MenuView;
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
}
