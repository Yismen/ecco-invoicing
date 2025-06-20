<?php

namespace App\Services\Filament\Forms;

use App\Services\InvoiceTemplatesService;
use Filament\Forms;

class ClientForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->autofocus()
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('invoice_template')
                ->options(InvoiceTemplatesService::make())
                ->searchable()
                ->preload()
                ->label('Invoice Template')
                ->required()
                ->default('default'),
            Forms\Components\TextInput::make('template_date_field_name')
                ->label('Date Field Name')
                ->required()
                ->default('File Sent At')
                ->placeholder('File Sent At'),
            Forms\Components\TextInput::make('template_project_field_name')
                ->label('Project Field Name')
                ->required()
                ->default('Publication')
                ->placeholder('Publication')
                ,
        ];
    }
}
