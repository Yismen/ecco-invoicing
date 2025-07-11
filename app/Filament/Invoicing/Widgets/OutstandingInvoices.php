<?php

namespace App\Filament\Invoicing\Widgets;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;
use App\Services\ModelListService;
use App\Filament\Exports\InvoiceExporter;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Concerns\CanPaginateRecords;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OutstandingInvoices extends BaseWidget
{
    use InteractsWithPageFilters;
    use CanPaginateRecords;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->query(
                Invoice::query()
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
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->label('Project')
                    ->searchable()
                    ->preload()
                    ->attribute('project_id')
                    ->options(
                        ModelListService::get(model: Project::query(), key_field: 'id', value_field: 'name')
                    ),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function(Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (Builder $query, $dateFrom) => $query->where('date', '>=', $dateFrom)
                            )->when(
                                $data['date_to'] ?? null,
                                fn (Builder $query, $dateTo) => $query->where('date', '<=', $dateTo)
                            );
                    }),
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
