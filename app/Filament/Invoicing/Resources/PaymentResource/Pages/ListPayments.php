<?php

namespace App\Filament\Invoicing\Resources\PaymentResource\Pages;

use App\Filament\Invoicing\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected static ?string $title = 'Invoice Payments';

    protected function getHeaderActions(): array
    {
        return [
            //     Actions\CreateAction::make(),
        ];
    }
}
