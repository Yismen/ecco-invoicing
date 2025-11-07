<?php

namespace App\Filament\Actions;

use Filament\Forms\Get;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Collection;

class PayBulkInvoicesAction
{
    public static function make()
    {
        return BulkAction::make('Pay Fully')
            ->label('Pay Multiple Invoices')
            ->color(Color::Teal)
            ->icon('heroicon-s-credit-card')
            ->size('xs')
            // ->checkIfRecordIsSelectableUsing(
            //     fn (\Illuminate\Database\Eloquent\Model $record): bool => $record->amount_pending > 0,
            // )
            ->modalHeading('Pay Multiple Invoices With The Total Pending Amount')
            ->modalDescription(
                'This action will pay the all selected invoices fully using the pending balance. It only pays invoices with any pending amount. Are you sure?'
            )
            ->deselectRecordsAfterCompletion()
            ->fillForm(fn(Collection $records): array => [
                'date' => now(),
                'amount_info' => $records->sum('balance_pending'),
                'selected_invoices' => $records
                    ->filter(fn ($record) => $record->balance_pending > 0)
                    ->sortByDesc('date')
                    ->pluck( 'balance_pending', 'number')
                    ->toArray(),
            ])
            ->form([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()->endOfDay()),
                        TextInput::make('reference'),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        FileUpload::make('images')
                            ->image()
                            ->imageEditor()
                            ->multiple()
                            ->maxSize(1024)
                            ->columnSpanFull(),
                        Placeholder::make('selected_invoices')
                            ->label('Selected Invoices')
                            ->content(fn (Get $get) => \view('filament.partials.invoices-selected-for-payment', [
                                    'selectedInvoices' => $get('selected_invoices'),
                                ])
                            )
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

                        $paidInvoices[] = '</br>' . $record->number;
                    }
                }

                Notification::make()
                    ->title('Bulk Payment Successful')
                    ->success()
                    ->body('Payments have been fully applied for the selected invoices: ' . implode(', ', $paidInvoices))
                    ->send();
            });
    }
}
