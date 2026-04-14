<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('tenant.id')
                    ->label('Tenant')
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge(),
                TextColumn::make('stripe_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active', 'free' => 'success',
                        'trialing' => 'info',
                        'past_due' => 'warning',
                        'canceled', 'incomplete_expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('trial_ends_at')
                    ->label('Trial hasta')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Finaliza')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->relationship('plan', 'name')
                    ->label('Plan'),
                SelectFilter::make('stripe_status')
                    ->options([
                        'active' => 'Activa',
                        'free' => 'Gratuita',
                        'trialing' => 'En trial',
                        'past_due' => 'Vencida',
                        'canceled' => 'Cancelada',
                    ])
                    ->label('Estado'),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
