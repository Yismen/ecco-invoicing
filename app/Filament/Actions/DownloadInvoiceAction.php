<?php

namespace App\Filament\Actions;

use App\Models\Invoice;
use Filament\Tables\Actions\Action;

class DownloadInvoiceAction
{
    public static function make(): Action
    {
        return Action::make(__('Pdf'))
            ->color('success')
            ->icon('heroicon-s-document-arrow-down')
            ->button()
            ->url(fn (Invoice $record) => route('generate-invoice', $record))
            ->openUrlInNewTab();
    }
}
