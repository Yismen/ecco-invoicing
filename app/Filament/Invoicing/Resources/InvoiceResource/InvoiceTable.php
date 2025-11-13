<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\InvoiceStatuses;
use App\Filament\Actions\CancelInvoiceAction;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\View\View;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Grid;
use App\Filament\Exports\InvoiceExporter;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Filament\Actions\PayBulkInvoicesAction;
use App\Filament\Actions\DownloadBulInvoicesAction;
use App\Filament\Actions\PayInvoiceAction;
use App\Filament\Actions\RestoreCancelledInvoiceAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Services\Filament\Filters\InvoiceTableFilters;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Query\Builder as QueryBuilder;

class InvoiceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid([
                'lg' => 2,
                // 'xl' => 3,
            ])
            ->columns([
                Stack::make([
                    Grid::make()
                        ->schema([
                            TextColumn::make('number')
                                ->copyable()
                                ->sortable()
                                ->searchable()
                                ->formatStateUsing(fn (string $state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Invoice Number'),
                                        'value' => $state
                                    ]
                                )),
                        ]),
                    Grid::make([
                        'sm' => 3
                    ])
                        ->schema([
                            TextColumn::make('date')
                                ->date()
                                ->sortable()
                                ->formatStateUsing(fn (string $state): View => view(
                                'filament.invoices.table.state', [
                                    'label' => __('Invoice Date'),
                                    'value' => Carbon::parse($state)->format('M d, Y')
                                ])),
                            TextColumn::make('due_date')
                                ->date()
                                ->sortable()
                                ->formatStateUsing(fn ($state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Due At'),
                                        'value' => $state->format('M d, Y')
                                    ])),
                            TextColumn::make('status')
                                ->badge()
                                ->color(fn($state) => $state->getColor())
                                ->formatStateUsing(fn (InvoiceStatuses $state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Invoice Date'),
                                        'value' => $state->getLabel()
                                    ])),
                        ]),
                    Grid::make([
                        'sm' => 3
                    ])
                        ->schema([
                            TextColumn::make('total_amount')
                                ->numeric()
                                ->sortable()
                                ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('total_amount') / 100)))
                                ->formatStateUsing(fn ($state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Total Amount'),
                                        'value' => Number::currency($state)
                                    ])),
                            TextColumn::make('total_paid')
                                ->numeric()
                                ->sortable()
                                ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('total_paid') / 100)))
                                ->formatStateUsing(fn ($state): View => view(
                                'filament.invoices.table.state', [
                                    'label' => __('Amount Paid'),
                                    'value' => Number::currency($state)
                                ])),
                            TextColumn::make('balance_pending')
                                ->label('Balance')
                                ->numeric()
                                ->color(fn (float $state) => $state == 0 ? Color::Green : Color::Red)
                                ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('balance_pending') / 100)))
                                ->formatStateUsing(fn ($state): View => view(
                                'filament.invoices.table.state', [
                                    'label' => __('Balance'),
                                    'value' => Number::currency($state)
                                ])),
                        ]),
                    Panel::make([
                        Grid::make([
                            'sm' => 2,
                            'xl' => 4
                        ])
                            ->columns(2)
                            ->schema([
                                TextColumn::make('project.client.name')
                                    ->label('Client')
                                    ->searchable()
                                    ->sortable()
                                    ->formatStateUsing(fn ($state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Client'),
                                        'value' => $state
                                    ])),
                                TextColumn::make('project.name')
                                    ->label('Project')
                                    ->searchable()
                                    ->sortable()
                                    ->formatStateUsing(fn ($state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Project'),
                                        'value' => $state
                                    ])),
                                TextColumn::make('agent.name')
                                    ->label('Agent')
                                    ->searchable()
                                    ->sortable()
                                    ->formatStateUsing(fn ($state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Agent'),
                                        'value' => $state
                                    ])),
                                TextColumn::make('campaign.name')
                                    ->label('Campaign')
                                    ->searchable()
                                    ->sortable()
                                    ->formatStateUsing(fn ($state): View => view(
                                    'filament.invoices.table.state', [
                                        'label' => __('Campaign'),
                                        'value' => $state
                                    ]))
                            ])
                    ])
                    ->collapsible(true)
                    ->collapsed(true)
                ])
                ->space(3)
            ])
            ->filters(InvoiceTableFilters::make())
            ->deferFilters()
            ->filtersFormWidth('lg')
            ->actionsAlignment(Alignment::End->value)
            ->actions([
                DownloadInvoiceAction::make(),
                ViewAction::make()
                    ->openUrlInNewTab()
                    ->button(),
                EditAction::make()
                        ->visible(fn($record) => $record->status !== InvoiceStatuses::Paid)
                        ->button()
                        ->modalWidth('7xl')
                        ->stickyModalHeader()
                        ->closeModalByClickingAway(false)
                        ->closeModalByEscaping(),
                PayInvoiceAction::make(),
                CancelInvoiceAction::make(),
                RestoreCancelledInvoiceAction::make(),
            ])
            ->recordUrl(null)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->label('Export Selected')
                    ->size('xs')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->color(Color::Amber)
                    ->deselectRecordsAfterCompletion()
                    ->exporter(InvoiceExporter::class),
                PayBulkInvoicesAction::make(),
                DownloadBulInvoicesAction::make(),
            ]);
    }
}
