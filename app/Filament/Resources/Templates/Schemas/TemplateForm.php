<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class TemplateForm
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
                        TextInput::make('component_name')
                            ->label('Nombre del componente')
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->helperText('No editable después de crear'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        TextInput::make('preview_image_url')
                            ->label('URL imagen preview')
                            ->url()
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
                Section::make('Configuración (JSON)')
                    ->schema([
                        KeyValue::make('config')
                            ->label('Config')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
