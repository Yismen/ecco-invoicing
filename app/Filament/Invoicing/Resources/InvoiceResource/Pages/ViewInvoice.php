<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use App\Filament\Actions\DownloadInvoiceAction;
use App\Filament\Actions\PayInvoiceAction;
use App\Filament\Invoicing\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            PayInvoiceAction::make(),
            DownloadInvoiceAction::make(),

        ];
    }
}
