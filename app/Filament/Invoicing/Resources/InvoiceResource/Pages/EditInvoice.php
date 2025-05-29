<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Actions\PayInvoiceAction;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Filament\Invoicing\Resources\InvoiceResource;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            PayInvoiceAction::make(),
            DownloadInvoiceAction::make(),
        ];
    }
}
