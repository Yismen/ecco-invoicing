<?php

namespace App\Services\Filament\Forms;

use Filament\Forms;

class ProjectForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            Forms\Components\Select::make('client_id')
                ->relationship('client', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Select a parent client')
                ->createOptionModalHeading('Create a new client')
                ->required(),
            Forms\Components\RichEditor::make('address')
                ->columnSpanFull()
                ->required(),
            Forms\Components\TextInput::make('invoice_net_days')
                ->label('Invoice Net Days')
                ->numeric()
                ->minValue(0)
                ->default(30)
                ->required(),
            Forms\Components\TextInput::make('tax_rate')
                ->label('Tax Rate (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(1)
                ->step(0.01)
                ->default(0)
                ->required(),
            Forms\Components\Textarea::make('invoice_notes')
                ->label('Invoice Notes')
                ->maxLength(255)
                ->columns(2),
            Forms\Components\Textarea::make('invoice_terms')
                ->label('Invoice Terms')
                ->maxLength(255)
                ->columns(2),
            ];
    }
}
