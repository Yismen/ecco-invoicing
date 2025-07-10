<?php

namespace App\Services\Filament\Forms;

use Filament\Forms;

class CampaignForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->autofocus()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\Select::make('agent_id')
                ->relationship('agent', 'name')
                ->createOptionForm(AgentForm::make())
                ->createOptionModalHeading('Create a new agent')
                ->searchable()
                ->preload(10)
                ->required(),
        ];
    }
}
