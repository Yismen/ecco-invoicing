<?php

namespace App\Filament\Actions;

use App\Models\Invoice;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class PayInvoiceAction
{
    public static function make(): Action
    {
        return Action::make('Pay')
            ->visible(fn ($record) => $record->balance_pending > 0)
            ->color(Color::Purple)
            ->icon(Heroicon::OutlinedCreditCard)
            ->button()
            ->schema(InvoicePaymentForm::make())
            ->action(function (array $data, Invoice $record): void {
                $record->payments()->create($data);
            });
    }
}
