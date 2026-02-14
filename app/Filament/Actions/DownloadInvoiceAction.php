<?php

namespace App\Filament\Actions;

use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadInvoiceAction
{
    public static function make(): Action
    {
        return Action::make(__('Pdf'))
            ->color('success')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->button()
            ->url(fn (Invoice $record) => route('generate-invoice', $record))
            ->openUrlInNewTab();
    }
}
