<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Widgets\ChartWidget;

class PlansDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribución por Plan';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $distribution = Subscription::whereIn('stripe_status', ['active', 'free', 'trialing'])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->selectRaw('plans.name, COUNT(*) as count')
            ->groupBy('plans.name')
            ->get();

        $colors = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];

        return [
            'datasets' => [
                [
                    'data' => $distribution->pluck('count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $distribution->count()),
                ],
            ],
            'labels' => $distribution->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
