<?php

namespace App\Filament\Invoicing\Widgets;

use App\Enums\InvoiceStatuses;
use App\Filament\Exports\InvoiceExporter;
use App\Filament\Invoicing\Resources\Invoices\InvoiceResource;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\Filament\Filters\InvoiceTableFilters;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\CanPaginateRecords;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
                    ->with('project.client', 'agent', 'campaign', 'cancellation')
                    ->where('balance_pending', '>', 0)
            )
            ->columns([
                TextColumn::make('number')
                    ->label('Invoice Number')
                    ->copyable()
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->badge()
                    ->color(fn ($state) => $state->getColor()),

                // Tables\Columns\TextColumn::make('project.client.name')
                //     ->label('Client')
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance_pending')
                    ->label('Balance Pending')
                    ->numeric()
                    ->color(Color::Red)
                    ->formatStateUsing(fn ($state) => Number::currency((-1) * $state, 'USD'))
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('balance_pending') / 100)))
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Expired')
                    ->date()
                    ->formatStateUsing(fn ($state) => $state?->diffForHumans())
                    ->sortable(),
            ])
            ->filters(
                InvoiceTableFilters::make(except: ['status'])
            )
            ->filtersFormWidth('lg')
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->color('primary')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record->getRouteKey()]))
                    ->openUrlInNewTab(),
            ])
            ->deferFilters()
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(InvoiceExporter::class)
                        ->label('Export Invoices')
                        ->icon(Heroicon::OutlinedFolder),
                ]),
            ]);
    }
}
