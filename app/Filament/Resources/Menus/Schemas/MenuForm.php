<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
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
