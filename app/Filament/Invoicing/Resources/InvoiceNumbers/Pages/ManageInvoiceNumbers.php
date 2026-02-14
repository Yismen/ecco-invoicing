<?php

namespace App\Filament\Invoicing\Resources\InvoiceNumbers\Pages;

use App\Filament\Invoicing\Resources\InvoiceNumbers\InvoiceNumberResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInvoiceNumbers extends ManageRecords
{
    protected static string $resource = InvoiceNumberResource::class;

    protected static ?string $title = 'Invoice Numbers';

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
