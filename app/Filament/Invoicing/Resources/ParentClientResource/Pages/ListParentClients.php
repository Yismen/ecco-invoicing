<?php

namespace App\Filament\Invoicing\Resources\ParentClientResource\Pages;

use App\Filament\Invoicing\Resources\ParentClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParentClients extends ListRecords
{
    protected static string $resource = ParentClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
