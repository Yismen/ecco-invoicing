<?php

namespace App\Filament\Invoicing\Resources\Invoices\Pages;

use App\Filament\Actions\DownloadInvoiceAction;
use App\Filament\Actions\PayInvoiceAction;
use App\Filament\Invoicing\Resources\Invoices\InvoiceResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            PayInvoiceAction::make(),
            DownloadInvoiceAction::make(),
        ];
    }
}
