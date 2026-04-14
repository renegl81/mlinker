<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('paid_at')
                    ->required(),
                TextInput::make('payment_method')
                    ->required(),
                TextInput::make('status')
                    ->required(),
            ]);
    }
}
