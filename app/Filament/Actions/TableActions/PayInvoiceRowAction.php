<?php

namespace App\Filament\Actions\TableActions;

use Filament\Forms;
use App\Models\Invoice;
use App\Rules\PreventOverpayment;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Tables\Actions\Action;

class PayInvoiceRowAction
{
    public static function make(): Action
    {
        return Action::make('Pay')
            ->visible(fn($record) => $record->balance_pending > 0)
            ->form(InvoicePaymentForm::make())
            ->action(function (array $data, Invoice $record): void {
                $record->payments()->create($data);
            });
    }
}
