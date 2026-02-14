<?php

namespace App\Services\Filament\Forms;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ProjectForm
{
    public static function make(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Select::make('client_id')
                ->relationship('client', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Select a parent client')
                ->createOptionModalHeading('Create a new client')
                ->required(),
            RichEditor::make('address')
                ->columnSpanFull()
                ->required(),
            TextInput::make('invoice_net_days')
                ->label('Invoice Net Days')
                ->numeric()
                ->minValue(0)
                ->default(30)
                ->required(),
            TextInput::make('tax_rate')
                ->label('Tax Rate (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(1)
                ->step(0.01)
                ->default(0)
                ->required(),
            Textarea::make('invoice_notes')
                ->label('Invoice Notes')
                ->maxLength(255)
                ->columns(2),
            Textarea::make('invoice_terms')
                ->label('Invoice Terms')
                ->maxLength(255)
                ->columns(2),
        ];
    }
}
