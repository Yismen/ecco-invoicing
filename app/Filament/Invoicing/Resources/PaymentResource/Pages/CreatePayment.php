<?php

namespace App\Filament\Invoicing\Resources\PaymentResource\Pages;

use App\Filament\Invoicing\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
