<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del tenant')
                    ->columns(2)
                    ->schema([
                        TextInput::make('id')
                            ->label('ID / Slug')
                            ->disabled(),
                        DateTimePicker::make('onboarding_completed_at')
                            ->label('Onboarding completado'),
                        TextInput::make('onboarding_step')
                            ->label('Paso de onboarding')
                            ->numeric(),
                    ]),
                Section::make('Stripe / Facturación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stripe_id')
                            ->label('Stripe ID')
                            ->disabled(),
                        TextInput::make('pm_type')
                            ->label('Tipo de pago')
                            ->disabled(),
                        TextInput::make('pm_last_four')
                            ->label('Últimos 4 dígitos')
                            ->disabled(),
                    ]),
            ]);
    }
}
