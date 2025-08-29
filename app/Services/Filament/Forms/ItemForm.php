<?php

namespace App\Services\Filament\Forms;

use Filament\Forms;

class ItemForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('campaign_id')
                ->relationship('campaign', 'name')
                ->required()
                ->searchable()
                ->createOptionForm(CampaignForm::make())
                ->createOptionModalHeading('Create Campaign')
                ->preload(10)
                ->placeholder('Select a campaign'),
            Forms\Components\TextInput::make('price')
                // ->minValue(0)
                ->required()
                ->numeric()
                ->inputMode('decimal')
                ->prefix('$'),

        ];
    }
}
