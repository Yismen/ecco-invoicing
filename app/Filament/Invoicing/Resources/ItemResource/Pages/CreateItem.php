<?php

namespace App\Filament\Invoicing\Resources\ItemResource\Pages;

use App\Filament\Invoicing\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;
}
