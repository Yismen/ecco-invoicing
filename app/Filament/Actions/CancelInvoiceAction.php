<?php

namespace App\Filament\Actions;

use App\Models\Invoice;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;

class CancelInvoiceAction
{
    public static function make()
    {
        return Action::make('Cancel')
            ->visible(fn($record) => $record->total_paid == 0 && $record->cancellation === null)
            ->color(Color::Red)
            ->icon('heroicon-s-archive-box-x-mark')
            ->button()
            ->form([
                DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->minDate(fn(Invoice $record) => $record->date)
                    ->maxDate(now()),
                Textarea::make('comments')
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
            });
    }
}
