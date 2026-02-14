<?php

namespace App\Filament\Invoicing\Resources\Invoices\Pages;

use App\Filament\Invoicing\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
