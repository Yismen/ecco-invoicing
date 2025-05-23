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
        ];
    }
}
