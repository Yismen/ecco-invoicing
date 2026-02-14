<?php

namespace App\Filament\Invoicing\Resources\Cancellations\Pages;

use App\Filament\Invoicing\Resources\Cancellations\CancellationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCancellations extends ManageRecords
{
    protected static string $resource = CancellationResource::class;

    protected static ?string $title = 'Invoice Cancellations';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
