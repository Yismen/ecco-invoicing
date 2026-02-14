<?php

namespace App\Filament\Actions;

use App\Enums\InvoiceStatuses;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class RestoreCancelledInvoiceAction
{
    public static function make()
    {
        return Action::make('restore')
            ->label('Restore Invoice')
            ->visible(fn ($record) => $record->status === InvoiceStatuses::Cancelled && $record->cancellation()->exists())
            ->color(Color::Green)
            ->icon(Heroicon::OutlinedArrowPath)
            ->button()
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
            });
    }
}
