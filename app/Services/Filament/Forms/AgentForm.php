<?php

namespace App\Services\Filament\Forms;

use Filament\Forms;

class AgentForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->autofocus()
                ->maxLength(255),
            Forms\Components\Select::make('client_id')
                ->relationship('client', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Select a client')
                ->createOptionForm(ClientForm::make())
                ->createOptionModalHeading('Create a new client')
                ->required(),
            Forms\Components\TextInput::make('phone')
                ->tel()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->maxLength(255),
        ];
    }
}
