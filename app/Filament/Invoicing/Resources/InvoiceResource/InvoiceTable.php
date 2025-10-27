<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Tables\Table;
use App\Enums\InvoiceStatuses;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use App\Filament\Exports\InvoiceExporter;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Services\Filament\Filters\InvoiceTableFilters;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection;

class InvoiceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
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
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('subtotal_amount') / 100)))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->money()
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('tax_amount') / 100)))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable()
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('total_amount') / 100)))
                    ->money(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->numeric()
                    ->sortable()
                    ->color(Color::Blue)
                    ->formatStateUsing(fn ($state) => $state > 0 ? Number::currency($state) : '')
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('total_paid') / 100))),
                Tables\Columns\TextColumn::make('balance_pending')
                    ->label('Balance')
                    ->numeric()
                    ->color(Color::Red)
                    ->summarize(Summarizer::make()->using(fn (QueryBuilder $query) => Number::currency($query->sum('balance_pending') / 100)))
                    ->formatStateUsing(fn ($state) => $state > 0 ? Number::currency($state * (-1)) : ''),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->badge()
                    ->color(fn ($state) => $state->getColor()),
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn ($record) => $record->status !== InvoiceStatuses::Paid)
                        ->modalWidth('7xl')
                        ->stickyModalHeader()
                        ->closeModalByClickingAway(false)
                        ->closeModalByEscaping(),
                    Tables\Actions\Action::make('Pay')
                        ->visible(fn ($record) => $record->balance_pending > 0)
                        ->color(Color::Purple)
                        ->icon('heroicon-s-credit-card')
                        ->form(InvoicePaymentForm::make())
                        ->action(function (array $data, Invoice $record): void {
                            $record->payments()->create($data);
                        }),
                    Tables\Actions\Action::make('Cancel')
                        ->visible(fn ($record) => $record->total_paid == 0 && !$record->cancellation()->exists())
                        ->color(Color::Red)
                        ->icon('heroicon-s-archive-box-x-mark')
                        ->form([
                            Forms\Components\DatePicker::make('date')
                                ->required()
                                ->default(now())
                                ->minDate(fn (Invoice $record) => $record->date)
                                ->maxDate(now()),
                            Forms\Components\Textarea::make('comments')
                                ->required()
                                ->minLength(10)
                                ->columnSpanFull()
                        ])
                        ->modalHeading(fn ($record) => "Cancel invoice {$record->number}")
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
                        ->visible(fn ($record) => $record->status === InvoiceStatuses::Cancelled && $record->cancellation()->exists())
                        ->color(Color::Green)
                        ->icon('heroicon-s-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => "Restore invoice {$record->number}")
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
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),

                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->label('Export Selected')
                    ->size('xs')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->color(Color::Amber)
                    ->deselectRecordsAfterCompletion()
                    ->exporter(InvoiceExporter::class),

                Tables\Actions\BulkAction::make('Pay Fully')
                    ->color(Color::Teal)
                    ->icon('heroicon-s-credit-card')
                    ->size('xs')
                    // ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalDescription(
                        'This action will pay the all selected invoices fully using the pending balance. It only pays invoices with any pending amount. Are you sure?'
                    )
                    // ->checkIfRecordIsSelectableUsing(
                    //     fn (Invoice $record): bool => $record->balance_pending > 0,
                    // )
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now()->endOfDay()),
                                Forms\Components\TextInput::make('reference'),
                                Forms\Components\Textarea::make('description')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('images')
                                    ->image()
                                    ->imageEditor()
                                    ->multiple()
                                    ->maxSize(1024)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $paidInvoices = [];
                        foreach ($records as $record) {
                            if ($record->balance_pending > 0) {
                                $record->payments()->create([
                                    'date' => $data['date'],
                                    'reference' => $data['reference'] ?? null,
                                    'description' => $data['description'] ?? null,
                                    'images' => $data['images'],
                                    'amount' => $record->balance_pending,
                                ]);

                                $paidInvoices[] = $record->number;
                            }
                        }

                        Notification::make()
                            ->title('Bulk Payment Successful')
                            ->success()
                            ->body('Payments have been fully applied for the selected invoices: ' . implode(', ', $paidInvoices))
                            ->send();
                    }),
            ]);
    }
}
