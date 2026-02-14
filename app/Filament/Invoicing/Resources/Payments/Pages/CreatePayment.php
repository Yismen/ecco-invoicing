<?php

namespace App\Filament\Invoicing\Resources\Payments\Pages;

use App\Filament\Invoicing\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
