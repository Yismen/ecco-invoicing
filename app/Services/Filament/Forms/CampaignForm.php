<?php

namespace App\Services\Filament\Forms;

use Closure;
use Filament\Forms;
use Filament\Forms\Get;
use App\Models\Campaign;

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
                    function(Get $get, $livewire, $record): Closure {
                        return function (string $attribute, $value, Closure $fail) use($get, $livewire, $record) {
                            $record_exists = Campaign::query()
                                ->where('name', $value)
                                ->where('agent_id', $livewire->data['agent_id'])
                                ->when($record, function ($query) use ($record) {
                                    return $query->where('id', '!=', $record->id);
                                })
                                ->exists();

                            if ($record_exists) {
                                $fail("The name {$value} already exits for this agent.");
                            }
                        };
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
