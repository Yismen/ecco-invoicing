<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use App\Filament\Invoicing\Resources\InvoiceNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvoiceNumbers extends ManageRecords
{
    protected static string $resource = InvoiceNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
