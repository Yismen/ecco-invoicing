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
            Forms\Components\Select::make('project_id')
                ->required()
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Select a project')
                ->createOptionForm(ClientForm::make())
                ->createOptionModalHeading('Create a new project'),
            Forms\Components\TextInput::make('phone')
                ->tel()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->maxLength(255),
        ];
    }
}
