<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información básica')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),

                Section::make('Límites')
                    ->columns(2)
                    ->schema([
                        TextInput::make('max_locations')
                            ->label('Máx. locales')
                            ->numeric()
                            ->default(1),
                        TextInput::make('max_menus_per_location')
                            ->label('Máx. menús por local')
                            ->numeric()
                            ->default(1),
                        TextInput::make('max_products')
                            ->label('Máx. productos')
                            ->numeric()
                            ->default(25),
                        TextInput::make('max_images')
                            ->label('Máx. imágenes')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('Funcionalidades')
                    ->columns(3)
                    ->schema([
                        Toggle::make('has_analytics')
                            ->label('Analítica'),
                        Toggle::make('has_custom_qr')
                            ->label('QR personalizado'),
                        Toggle::make('has_multilang')
                            ->label('Multi-idioma'),
                        Toggle::make('has_catalog')
                            ->label('Catálogo'),
                        Toggle::make('has_api_access')
                            ->label('Acceso API'),
                        Toggle::make('has_custom_domain')
                            ->label('Dominio propio'),
                        Toggle::make('show_branding')
                            ->label('Mostrar branding'),
                    ]),

                Section::make('Facturación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        TextInput::make('period')
                            ->label('Periodo')
                            ->required()
                            ->helperText('Ej: monthly, yearly'),
                        TextInput::make('trial_days')
                            ->label('Días de prueba')
                            ->numeric()
                            ->default(0),
                        TextInput::make('stripe_price_id')
                            ->label('Stripe Price ID'),
                    ]),
            ]);
    }
}
