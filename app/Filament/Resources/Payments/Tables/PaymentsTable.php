<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('subscription.tenant.id')
                    ->label('Tenant')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Importe')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label('Método')
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('paid_at', 'desc');
    }
}
