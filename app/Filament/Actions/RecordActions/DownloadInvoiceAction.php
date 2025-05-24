<?php

namespace App\Filament\Actions\RecordActions;

use App\Models\Invoice;
use Filament\Actions\Action;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Invoice as PrintInvoice;

class DownloadInvoiceAction
{
    public static function make(): Action
    {
        return Action::make(__('Download'))
                    ->color('success')
                    ->icon('heroicon-s-document-arrow-down')
                    ->url(fn (Invoice $record) => route('generate-invoice', $record))
                    ->openUrlInNewTab();
    }
}
