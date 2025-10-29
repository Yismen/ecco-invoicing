<?php

namespace App\Services\Filament\Forms;

use Closure;
use Filament\Forms;
use App\Models\Agent;
use App\Rules\UniqueByParentRelationship;

class AgentForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->rules([
                    'string',
                    'max:255',
                    function($livewire, $record) {
                        $projectId = $livewire->data['project_id'] ?? $livewire->mountedActionsData[0]['project_id'] ?? $record->project_id;

                        return new UniqueByParentRelationship(
                            table: Agent::class,
                            uniqueField: 'name',
                            parentField: 'project_id',
                            parentId: $projectId,
                            recordToIgnore: $record
                        );
                    },
                ])
                ->autofocus()
                ->maxLength(255),
            Forms\Components\Select::make('project_id')
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Select a project')
                ->createOptionForm(ClientForm::make())
                ->createOptionModalHeading('Create a new project')
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
