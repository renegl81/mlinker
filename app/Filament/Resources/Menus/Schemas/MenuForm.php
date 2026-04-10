<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('location_id')
                    ->relationship('location', 'name')
                    ->required(),
                Select::make('template_id')
                    ->relationship('template', 'name')
                    ->required(),
                FileUpload::make('image_url')
                    ->image(),
                Toggle::make('show_prices')
                    ->required(),
                Toggle::make('show_currency')
                    ->required(),
                Toggle::make('show_calories')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('tenant_id')
                    ->required(),
                TextInput::make('translations'),
            ]);
    }
}
