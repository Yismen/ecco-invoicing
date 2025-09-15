<?php

namespace App\Services\Filament\Filters;

use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Item;
use App\Models\Project;
use App\Services\ModelListService;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\InvoiceStatuses;

class InvoiceTableFilters
{
    public static function make(array $except = []): array
    {
        $filters = [
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('from')
                                ->label('From Date'),
                            Forms\Components\DatePicker::make('to')
                                ->label('To Date')
                                ->maxDate(now()),
                        ])
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['from']) {
                            $query->whereDate('date', '>=', $data['from']);
                        }

                        if ($data['to']) {
                            $query->whereDate('date', '<=', $data['to']);
                        }
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . date('M j, Y', strtotime($data['from']));
                        }

                        if ($data['to'] ?? null) {
                            $indicators['to'] = 'To ' . date('M j, Y', strtotime($data['to']));
                        }

                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(
                        ModelListService::get(
                            model: Project::query(),
                            key_field: 'id',
                            value_field: 'name'
                        )
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(ModelListService::get(
                        model: Agent::query(),
                        key_field: 'id',
                        value_field: 'name'
                    )),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campaign')
                    ->options(ModelListService::get(
                        model: Campaign::query(),
                        key_field: 'id',
                        value_field: 'name'
                    ))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('invoiceItems')
                    ->label('Item')
                    ->options(ModelListService::get(
                        model: Item::query(),
                        key_field: 'id',
                        value_field: 'name'
                    ))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): void {
                        if (count($data['values'] ?? []) === 0) {
                            return;
                        }

                        $query->whereHas('invoiceItems', function (Builder $query) use ($data): void {
                            $query->whereIn('item_id', $data['values'] );
                        });
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(InvoiceStatuses::toArray())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                ];

        return array_filter($filters, function ($filter) use ($except) {
            return ! in_array($filter->getName(), $except);
        });
    }
}
