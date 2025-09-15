<?php

namespace App\Filament\Invoicing\Resources\InvoiceCancellationResource\Pages;

use App\Filament\Invoicing\Resources\InvoiceCancellationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvoiceCancellations extends ManageRecords
{
    protected static string $resource = InvoiceCancellationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
