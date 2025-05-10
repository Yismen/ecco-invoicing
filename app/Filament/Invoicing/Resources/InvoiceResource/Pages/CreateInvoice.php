<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use App\Filament\Invoicing\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
