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
            Forms\Components\Select::make('project_id')
                ->relationship('project', 'name')
                ->required()
                ->searchable()
                ->createOptionForm(ProjectForm::make())
                ->createOptionModalHeading('Create Project')
                ->preload(10)
                ->placeholder('Select a project'),
            Forms\Components\TextInput::make('price')
                ->minValue(0)
                ->required()
                ->numeric()
                ->prefix('$'),

        ];
    }
}
