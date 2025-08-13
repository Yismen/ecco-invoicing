<?php

namespace App\Services\Filament\Forms;

use Closure;
use Filament\Forms;
use App\Models\Agent;
use Filament\Forms\Get;

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
                    function(Get $get, $livewire, $record): Closure {
                        return function (string $attribute, $value, Closure $fail) use($get, $livewire, $record) {
                            $record_exists = Agent::query()
                                ->where('name', $value)
                                ->where('project_id', $livewire->data['project_id'])
                                ->when($record, function ($query) use ($record) {
                                    return $query->where('id', '!=', $record->id);
                                })
                                ->exists();

                            if ($record_exists) {
                                $fail("The name {$value} already exits for this project.");
                            }
                        };
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
