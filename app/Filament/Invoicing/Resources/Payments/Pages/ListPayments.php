<?php

namespace App\Filament\Invoicing\Resources\Payments\Pages;

use App\Filament\Invoicing\Resources\Payments\PaymentResource;
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
