<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Tables\Table;
use App\Enums\InvoiceStatuses;
use App\Filament\Actions\DownloadBulInvoicesAction;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use App\Filament\Exports\InvoiceExporter;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Filament\Actions\PayBulkInvoicesAction;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Services\Filament\Filters\InvoiceTableFilters;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class InvoiceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->copyable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('subtotal_amount') / 100)))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('tax_amount') / 100)))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable()
                    ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('total_amount') / 100)))
                    ->money(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->numeric()
                    ->sortable()
                    ->color(Color::Blue)
                    ->formatStateUsing(fn($state) => $state > 0 ? Number::currency($state) : '')
                    ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('total_paid') / 100))),
                Tables\Columns\TextColumn::make('balance_pending')
                    ->label('Balance')
                    ->numeric()
                    ->color(Color::Red)
                    ->summarize(Summarizer::make()->using(fn(QueryBuilder $query) => Number::currency($query->sum('balance_pending') / 100)))
                    ->formatStateUsing(fn($state) => $state > 0 ? Number::currency($state * (-1)) : ''),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->badge()
                    ->color(fn($state) => $state->getColor()),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(InvoiceTableFilters::make())
            ->deferFilters()
            ->filtersFormWidth('lg')
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => $record->status !== InvoiceStatuses::Paid)
                        ->modalWidth('7xl')
                        ->stickyModalHeader()
                        ->closeModalByClickingAway(false)
                        ->closeModalByEscaping(),
                    Tables\Actions\Action::make('Pay')
                        ->visible(fn($record) => $record->balance_pending > 0)
                        ->color(Color::Purple)
                        ->icon('heroicon-s-credit-card')
                        ->form(InvoicePaymentForm::make())
                        ->action(function (array $data, Invoice $record): void {
                            $record->payments()->create($data);
                        }),
                    Tables\Actions\Action::make('Cancel')
                        ->visible(fn($record) => $record->total_paid == 0 && $record->cancellation === null)
                        ->color(Color::Red)
                        ->icon('heroicon-s-archive-box-x-mark')
                        ->form([
                            Forms\Components\DatePicker::make('date')
                                ->required()
                                ->default(now())
                                ->minDate(fn(Invoice $record) => $record->date)
                                ->maxDate(now()),
                            Forms\Components\Textarea::make('comments')
                                ->required()
                                ->minLength(10)
                                ->columnSpanFull()
                        ])
                        ->modalHeading(fn($record) => "Cancel invoice {$record->number}")
                        ->modalDescription('Are you 100% certain you want to cancel this invoice?')
                        ->action(function (array $data, Invoice $record): void {
                            $record->cancellation()->create($data);

                            Notification::make()
                                ->danger()
                                ->title('Invoice Cancelled')
                                ->body("Invoice {$record->number} has been cancelled!")
                                ->send();
                        }),
                    Tables\Actions\Action::make('restore')
                        ->label('Restore Invoice')
                        ->visible(fn($record) => $record->status === InvoiceStatuses::Cancelled && $record->cancellation()->exists())
                        ->color(Color::Green)
                        ->icon('heroicon-s-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => "Restore invoice {$record->number}")
                        ->modalDescription('Are you 100% certain you want to restore/remove cancellation for this invoice?')
                        ->action(function (array $data, Invoice $record): void {
                            $record->cancellation->delete();

                            Notification::make()
                                ->warning()
                                ->title('Invoice restored')
                                ->body("Invoice {$record->number} has been restored!")
                                ->send();
                        }),
                    DownloadInvoiceAction::make(),
                ])
                    ->icon('heroicon-o-bars-3-center-left')
                    ->tooltip('Actions'),
            ])
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
