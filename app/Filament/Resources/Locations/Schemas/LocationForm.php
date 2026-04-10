<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('address')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('province')
                    ->required(),
                TextInput::make('postal_code')
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
                FileUpload::make('image_url')
                    ->image(),
                TextInput::make('logo_url')
                    ->url(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required(),
                TextInput::make('lang')
                    ->required(),
                TextInput::make('languages'),
                TextInput::make('currency')
                    ->required(),
                TextInput::make('time_format')
                    ->required(),
                TextInput::make('time_zone')
                    ->required(),
                TextInput::make('social_medias'),
                TextInput::make('tenant_id')
                    ->required(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
            ]);
    }
}
