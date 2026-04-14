<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label('País')
                    ->searchable(),
                TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->searchable(),
                TextColumn::make('menus_count')
                    ->label('Menús')
                    ->counts('menus')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->relationship('country', 'name')
                    ->label('País'),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
