<?php

namespace App\Filament\Actions;

use App\Models\Invoice;
use App\Services\Filament\Forms\InvoicePaymentForm;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;

class PayInvoiceAction
{
    public static function make(): Action
    {
        return Action::make('Pay')
            ->visible(fn ($record) => $record->balance_pending > 0)
            ->color(Color::Purple)
            ->icon('heroicon-s-credit-card')
            ->form(InvoicePaymentForm::make())
            ->action(function (array $data, Invoice $record): void {
                $record->payments()->create($data);
            });
    }
}
