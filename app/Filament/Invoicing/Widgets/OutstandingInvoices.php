<?php

namespace App\Filament\Invoicing\Widgets;

use App\Filament\Exports\InvoiceExporter;
use App\Models\Invoice;
use App\Services\InvoiceQueryForFilters;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class OutstandingInvoices extends BaseWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->query(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query(),
                    $this->filters
                )
                    ->where('status', '=', \App\Enums\InvoiceStatuses::Overdue)
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
                Tables\Columns\TextColumn::make('project.client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Expired')
                    ->date()
                    ->formatStateUsing(fn ($state) => $state?->diffForHumans())
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->color('primary')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Invoice $record): string => route('filament.invoicing.resources.invoices.view', ['record' => $record->id]))
                    ->openUrlInNewTab(),
            ])
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
