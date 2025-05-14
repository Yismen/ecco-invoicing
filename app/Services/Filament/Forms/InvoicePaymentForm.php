<?php

namespace App\Services\Filament\Forms;

use Filament\Forms;
use App\Rules\PreventOverpayment;

class InvoicePaymentForm
{
    public static function make(): array
    {
        return [
                Forms\Components\Section::make()
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn($record) => $record->balance_pending)
                            ->default(fn($record) => $record->balance_pending)
                            ->rule(static function ($record) {
                                return new PreventOverpayment($record->id ?? null);
                            }),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->minDate(fn($record) => $record->date)
                            ->maxDate(today()),
                        Forms\Components\TextInput::make('reference')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('images')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])
            ];
    }
}
