<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Widgets\ChartWidget;

class TenantsGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Crecimiento de Tenants (últimos 12 meses)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Tenant::selectRaw("DATE_TRUNC('month', created_at) as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $labels[] = $month->format('M Y');
            $key = $month->format('Y-m-01 00:00:00');
            $found = $data->firstWhere('month', $key);
            $values[] = $found ? (int) $found->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nuevos tenants',
                    'data' => $values,
                    'borderColor' => '#6366f1',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
