<?php

namespace App\Filament\Invoicing\Resources\CancellationResource\Pages;

use App\Filament\Invoicing\Resources\CancellationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCancellations extends ManageRecords
{
    protected static string $resource = CancellationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
