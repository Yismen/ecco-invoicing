<?php

namespace App\Filament\Actions;

use App\Models\Invoice;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use App\Services\Filament\Forms\InvoicePaymentForm;

class PayInvoiceAction
{
    public static function make(): Action
    {
        return Action::make('Pay')
            ->visible(fn ($record) => $record->balance_pending > 0)
            ->color(Color::Purple)
            ->icon('heroicon-s-credit-card')
            ->button()
            ->form(InvoicePaymentForm::make())
            ->action(function (array $data, Invoice $record): void {
                $record->payments()->create($data);
            });
    }
}
