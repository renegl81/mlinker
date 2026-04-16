<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use App\Models\Plan;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ListPlans extends Page
{
    protected static string $resource = PlanResource::class;

    public function getView(): string
    {
        return 'filament.resources.plans.pages.list-plans';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Planes';
    }

    public function getPlans(): Collection
    {
        return Plan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->get();
    }

    public function toggleActive(int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        $plan->update(['is_active' => ! $plan->is_active]);
    }
}
