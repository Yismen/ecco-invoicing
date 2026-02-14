<?php

namespace App\Services\Filament\Forms;

use App\Models\Campaign;
use App\Rules\UniqueByParentRelationship;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class CampaignForm
{
    public static function make(): array
    {
        return [
            TextInput::make('name')
                ->autofocus()
                ->required()
                ->rules([
                    'string',
                    'max:255',
                    function ($livewire, $record) {
                        $parentId = $livewire->data['agent_id'] ??
                            $livewire->mountedActionsData[0]['agent_id'] ??
                            $livewire->mountedTableActionsData[0]['agent_id'] ??
                            null;

                        return new UniqueByParentRelationship(
                            table: Campaign::class,
                            uniqueField: 'name',
                            parentField: 'agent_id',
                            parentId: $parentId,
                            recordToIgnore: $record
                        );
                    },
                ])
                ->maxLength(255),
            Select::make('agent_id')
                ->relationship('agent', 'name')
                ->createOptionForm(AgentForm::make())
                ->createOptionModalHeading('Create a new agent')
                ->searchable()
                ->preload(10)
                ->required(),
        ];
    }
}
