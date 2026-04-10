<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Plan;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePlan')
                ->label('Cambiar plan')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->form([
                    Select::make('plan_id')
                        ->label('Plan')
                        ->options(Plan::where('is_active', true)->pluck('name', 'id'))
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $tenant = $this->record;
                    $plan = Plan::findOrFail($data['plan_id']);

                    $subscription = Subscription::updateOrCreate(
                        ['tenant_id' => $tenant->id],
                        [
                            'plan_id' => $plan->id,
                            'type' => 'default',
                            'stripe_status' => $plan->price == 0 ? 'free' : 'active',
                            'stripe_price' => $plan->stripe_price_id ?? '',
                            'quantity' => 1,
                        ]
                    );

                    Notification::make()
                        ->title('Plan actualizado')
                        ->body("El tenant {$tenant->id} ahora está en el plan {$plan->name}")
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Tenant: '.$this->record->id;
    }
}
