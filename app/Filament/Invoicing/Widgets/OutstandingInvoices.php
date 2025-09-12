<?php

namespace App\Filament\Invoicing\Widgets;

use App\Enums\InvoiceStatuses;
use App\Filament\Exports\InvoiceExporter;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\ModelListService;
use Filament\Forms;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Concerns\CanPaginateRecords;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

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
                    ->with('project.client', 'agent', 'campaign')
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
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->color('primary')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Invoice $record): string => route('filament.invoicing.resources.invoices.view', ['record' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->filtersFormColumns(2)
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
