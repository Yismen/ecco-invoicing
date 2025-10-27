<?php

namespace App\Filament\Invoicing\Widgets;

use Filament\Forms;
use Filament\Tables;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use App\Enums\InvoiceStatuses;
use Illuminate\Support\Number;
use App\Services\ModelListService;
use Filament\Support\Colors\Color;
use App\Filament\Exports\InvoiceExporter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Concerns\CanPaginateRecords;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Filament\Invoicing\Resources\InvoiceResource;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OutstandingInvoices extends BaseWidget
{
    use CanPaginateRecords;
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->poll('600s')
            ->query(
                Invoice::query()
                    ->whereIn('status', [
                        InvoiceStatuses::Pending->value,
                        InvoiceStatuses::PartiallyPaid->value,
                        InvoiceStatuses::Overdue->value,
                    ])
                    ->with('project.client', 'agent', 'campaign', 'cancellation')
                    ->where('balance_pending', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->badge()
                    ->color(fn ($state) => $state->getColor()),

                // Tables\Columns\TextColumn::make('project.client.name')
                //     ->label('Client')
                //     ->searchable()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_pending')
                    ->label('Balance Pending')
                    ->numeric()
                    ->color(Color::Red)
                    ->formatStateUsing(fn ($state) => Number::currency((-1) * $state , 'USD'))
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('balance_pending') / 100)))
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Expired')
                    ->date()
                    ->formatStateUsing(fn ($state) => $state?->diffForHumans())
                    ->sortable(),
            ])
            ->filters(
                \App\Services\Filament\Filters\InvoiceTableFilters::make(except: ['status'])
            )
            ->filtersFormWidth('lg')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->color('primary')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record->getRouteKey()]))
                    ->openUrlInNewTab(),
            ])
            ->deferFilters()
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(InvoiceExporter::class)
                        ->label('Export Invoices')
                        ->icon('heroicon-o-download'),
                ]),
            ]);
    }
}
