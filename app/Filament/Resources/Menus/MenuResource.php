<?php

namespace App\Filament\Resources\Menus;

use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Filament\Resources\Menus\Schemas\MenuForm;
use App\Filament\Resources\Menus\Tables\MenusTable;
use App\Models\Menu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Stancl\Tenancy\Database\TenantScope;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Menús';

    protected static ?string $modelLabel = 'Menú';

    protected static ?string $pluralModelLabel = 'Menús';

    protected static ?int $navigationSort = 7;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(TenantScope::class);
    }

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
        ];
    }
}
