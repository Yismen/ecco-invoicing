<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Enums\InvoiceStatuses;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Actions\PayInvoiceAction;
use App\Filament\Actions\DownloadInvoiceAction;
use App\Filament\Invoicing\Resources\InvoiceResource;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status !== InvoiceStatuses::Paid),
            PayInvoiceAction::make(),
            DownloadInvoiceAction::make(),

        ];
    }
}
