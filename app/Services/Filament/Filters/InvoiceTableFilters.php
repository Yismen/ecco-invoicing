<?php

namespace App\Services\Filament\Filters;

use App\Enums\InvoiceStatuses;
use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Item;
use App\Models\Project;
use App\Services\ModelList\Conditions\WhereInCondition;
use App\Services\ModelListService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;

class InvoiceTableFilters
{
    public static function make(array $except = []): array
    {
        $filters = [
            TrashedFilter::make(),
            Filter::make('options')
                ->columns(2)
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['from'] ?? null) {
                        $indicators['from'] = 'From '.date('M j, Y', strtotime($data['from']));
                    }

                    if ($data['to'] ?? null) {
                        $indicators['to'] = 'To '.date('M j, Y', strtotime($data['to']));
                    }

                    if ($data['client_id'] ?? null) {
                        $indicators['client_id'] = 'Client: '.\implode(', ', ModelListService::get(
                            model: Client::query(),
                            key_field: 'id',
                            value_field: 'name',
                            conditions: [
                                new WhereInCondition('id', $data['client_id']),
                            ]
                        ));
                    }

                    if ($data['project_id'] ?? null) {
                        $indicators['project_id'] = 'Project: '.\implode(', ', ModelListService::get(
                            model: Project::query(),
                            key_field: 'id',
                            value_field: 'name',
                            conditions: [
                                new WhereInCondition('id', $data['project_id']),
                            ]
                        ));
                    }

                    if ($data['agent_id'] ?? null) {
                        $indicators['agent_id'] = 'Agent: '.\implode(', ', ModelListService::get(
                            model: Agent::query(),
                            key_field: 'id',
                            value_field: 'name',
                            conditions: [
                                new WhereInCondition('id', $data['agent_id']),
                            ]
                        ));
                    }

                    if ($data['campaign_id'] ?? null) {
                        $indicators['campaign_id'] = 'Campaign: '.\implode(', ', ModelListService::get(
                            model: Campaign::query(),
                            key_field: 'id',
                            value_field: 'name',
                            conditions: [
                                new WhereInCondition('id', $data['campaign_id']),
                            ]
                        ));
                    }

                    if ($data['invoiceItems'] ?? null) {
                        $indicators['invoiceItems'] = 'Item: '.\implode(', ', ModelListService::get(
                            model: Item::query(),
                            key_field: 'id',
                            value_field: 'name',
                            conditions: [
                                new WhereInCondition('id', $data['invoiceItems']),
                            ]
                        ));
                    }

                    if ($data['status'] ?? null) {
                        $indicators['status'] = 'Status: '.\implode(', ', $data['status']);
                    }

                    return $indicators;
                })
                ->schema([
                    DatePicker::make('from')
                        ->live()
                        ->label('From Date')
                        ->maxDate(now()),
                    DatePicker::make('to')
                        ->live()
                        ->label('To Date')
                        ->minDate(fn (Get $get) => $get('from'))
                        ->maxDate(now()),
                    Select::make('client_id')
                        ->label('Client')
                        ->live()
                        ->options(
                            ModelListService::get(
                                model: Client::query(),
                                key_field: 'id',
                                value_field: 'name'
                            )
                        )
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('project_id')
                        ->label('Project')
                        ->live()
                        ->options(function (Get $get) {
                            return ModelListService::get(
                                model: Project::query(),
                                key_field: 'id',
                                value_field: 'name',
                                conditions: count($get('client_id')) === 0 ?
                                    [] :
                                    [
                                        new WhereInCondition('client_id', $get('client_id')),
                                    ]
                            );
                        })
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('agent_id')
                        ->label('Agent')
                        ->live()
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get) {
                            return ModelListService::get(
                                model: Agent::query(),
                                key_field: 'id',
                                value_field: 'name',
                                conditions: count($get('project_id')) === 0 ?
                                    [] :
                                    [
                                        new WhereInCondition('project_id', $get('project_id')),
                                    ]
                            );
                        }),
                    Select::make('campaign_id')
                        ->label('Campaign')
                        ->live()
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get) {
                            return ModelListService::get(
                                model: Campaign::query(),
                                key_field: 'id',
                                value_field: 'name',
                                conditions: count($get('agent_id')) === 0 ?
                                    [] :
                                    [
                                        new WhereInCondition('agent_id', $get('agent_id')),
                                    ]
                            );
                        }),
                    Select::make('invoiceItems')
                        ->label('Item')
                        ->live()
                        ->options(function (Get $get) {
                            return ModelListService::get(
                                model: Item::query(),
                                key_field: 'id',
                                value_field: 'name',
                                conditions: count($get('campaign_id')) === 0 ?
                                    [] :
                                    [
                                        new WhereInCondition('campaign_id', $get('campaign_id')),
                                    ]
                            );
                        })
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->label('Status')
                        ->live()
                        ->options(InvoiceStatuses::toArray())
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ])
                ->query(function (Builder $query, array $data): void {
                    $query
                        ->when(
                            $data['from'] ?? null,
                            function ($query) use ($data) {
                                $query->whereDate('date', '>=', $data['from']);
                            }
                        )
                        ->when(
                            $data['to'] ?? null,
                            function ($query) use ($data) {
                                $query->whereDate('date', '<=', $data['to']);
                            }
                        )
                        ->when(
                            $data['client_id'] ?? null && count($data['client_id']) > 0,
                            function ($query) use ($data) {
                                $query->whereHas('project', function ($query) use ($data) {
                                    $query->whereIn('client_id', $data['client_id']);
                                });
                            }
                        )
                        ->when(
                            $data['project_id'] ?? null && count($data['project_id']) > 0,
                            function ($query) use ($data) {
                                $query->whereIn('project_id', $data['project_id']);
                            }
                        )
                        ->when(
                            $data['agent_id'] ?? null && count($data['agent_id']) > 0,
                            function ($query) use ($data) {
                                $query->whereIn('agent_id', $data['agent_id']);
                            }
                        )
                        ->when(
                            $data['campaign_id'] ?? null && count($data['campaign_id']) > 0,
                            function ($query) use ($data) {
                                $query->whereIn('campaign_id', $data['campaign_id']);
                            }
                        )
                        ->when(
                            $data['status'] ?? null && count($data['status']) > 0,
                            function ($query) use ($data) {
                                $query->whereIn('status', $data['status']);
                            }
                        )
                        ->when(
                            $data['invoiceItems'] ?? null && count($data['invoiceItems']) > 0,
                            function ($query) use ($data) {
                                $query->whereHas('invoiceItems', function ($query) use ($data) {
                                    $query->whereIn('item_id', $data['invoiceItems']);
                                });
                            }
                        );
                }),
        ];

        if (count($except) === 0) {
            return $filters;
        }

        // filter out the excepted filters from the options filters
        $filteredFilters = [];
        foreach ($filters as $filter) {
            if (! in_array($filter->getName(), $except)) {
                $filteredFilters[$filter->getName()] = $filter;
            }
            $schemas = \array_filter($filter->getFormSchema(), function ($component) use ($except) {
                if (method_exists($component, 'getName')) {
                    return ! in_array($component->getName(), $except);
                }

                return true;
            });
            $filteredFilters[$filter->getName()]->schema($schemas);
        }

        return $filteredFilters;
    }
}
