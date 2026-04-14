<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID / Slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domains.domain')
                    ->label('Dominio principal')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('subscription.plan.name')
                    ->label('Plan')
                    ->badge()
                    ->default('Sin plan'),
                TextColumn::make('subscription.stripe_status')
                    ->label('Estado sub.')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active', 'free' => 'success',
                        'trialing' => 'info',
                        'past_due' => 'warning',
                        'canceled', 'incomplete_expired' => 'danger',
                        default => 'gray',
                    })
                    ->default('Sin suscripción'),
                IconColumn::make('onboarding_completed_at')
                    ->label('Onboarding')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! is_null($record->onboarding_completed_at)),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('onboarding_completed')
                    ->label('Onboarding completado')
                    ->nullable()
                    ->attribute('onboarding_completed_at'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
