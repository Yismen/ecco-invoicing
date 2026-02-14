<?php

namespace App\Services\Filament\Forms;

use App\Models\Agent;
use App\Rules\UniqueByParentRelationship;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class AgentForm
{
    public static function make(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->rules([
                    'string',
                    'max:255',
                    function ($livewire, $record) {
                        $parentId = $livewire->data['project_id'] ??
                            $livewire->mountedActionsData[0]['project_id'] ??
                            $livewire->mountedTableActionsData[0]['project_id'] ??
                            null;

                        return new UniqueByParentRelationship(
                            table: Agent::class,
                            uniqueField: 'name',
                            parentField: 'project_id',
                            parentId: $parentId,
                            recordToIgnore: $record
                        );
                    },
                ])
                ->autofocus()
                ->maxLength(255),
            Select::make('project_id')
                ->required()
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Select a project')
                ->createOptionForm(ClientForm::make())
                ->createOptionModalHeading('Create a new project'),
            TextInput::make('phone')
                ->tel()
                ->maxLength(255),
            TextInput::make('email')
                ->email()
                ->maxLength(255),
        ];
    }
}
