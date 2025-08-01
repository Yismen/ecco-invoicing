<?php

namespace App\Filament\Invoicing\Resources\ClientResource\Pages;

use App\Filament\Invoicing\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClients extends ManageRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
