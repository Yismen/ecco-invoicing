<?php

namespace App\Services\Filament\Forms;

use App\Rules\PreventOverpayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class InvoicePaymentForm
{
    public static function make(): array
    {
        return [
            Section::make()
                ->columns(3)
                ->schema([
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->inputMode('decimal')
                        ->minValue(0)
                        ->maxValue(fn ($record) => $record->balance_pending)
                        ->default(fn ($record) => $record->balance_pending)
                        ->rule(static function ($record) {
                            return new PreventOverpayment($record);
                        }),
                    DatePicker::make('date')
                        ->required()
                        ->default(now())
                        ->minDate(fn ($record) => $record->date)
                        ->maxDate(today()),
                    TextInput::make('reference')
                        ->maxLength(255),
                    FileUpload::make('images')
                        ->image()
                        ->imageEditor()
                        ->multiple()
                        ->maxSize(1024)
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
