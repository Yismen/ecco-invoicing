<?php

namespace App\Services\Filament\Forms;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ItemForm
{
    public static function make(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Select::make('campaign_id')
                ->relationship('campaign', 'name')
                ->required()
                ->searchable()
                ->createOptionForm(CampaignForm::make())
                ->createOptionModalHeading('Create Campaign')
                ->preload(10)
                ->placeholder('Select a campaign'),
            TextInput::make('price')
                ->required()
                ->numeric()
                ->inputMode('decimal')
                ->prefix('$'),

        ];
    }
}
