<?php

namespace App\Services\Filament\Forms;

use Closure;
use Filament\Forms;
use App\Models\Campaign;
use App\Rules\UniqueByParentRelationship;

class CampaignForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->autofocus()
                ->required()
                ->rules([
                    'string',
                    'max:255',
                    function($livewire, $record) {
                        $parentId = $livewire->data['agent_id'] ?? $livewire->mountedActionsData[0]['agent_id'] ?? $record->agent_id;

                        return new UniqueByParentRelationship(
                            table: Campaign::class,
                            uniqueField: 'name',
                            parentField: 'agent_id',
                            parentId: $parentId,
                            recordToIgnore: $record
                        );
                    },
                ])
                ->maxLength(255)
                ,
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
