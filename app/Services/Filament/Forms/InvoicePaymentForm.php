<?php

namespace App\Services\Filament\Forms;

use App\Rules\PreventOverpayment;
use Filament\Forms;

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
                        ->inputMode('decimal')
                        ->minValue(0)
                        ->maxValue(fn ($record) => $record->balance_pending)
                        ->default(fn ($record) => $record->balance_pending)
                        ->rule(static function ($record) {
                            return new PreventOverpayment($record);
                        }),
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->default(now())
                        ->minDate(fn ($record) => $record->date)
                        ->maxDate(today()),
                    Forms\Components\TextInput::make('reference')
                        ->maxLength(255),
                    Forms\Components\FileUpload::make('images')
                        ->image()
                        ->imageEditor()
                        ->multiple()
                        ->maxSize(1024)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
