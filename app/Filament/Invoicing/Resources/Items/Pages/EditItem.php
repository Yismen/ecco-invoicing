<?php

namespace App\Filament\Invoicing\Resources\Items\Pages;

use App\Filament\Invoicing\Resources\Items\ItemResource;
use Filament\Actions;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            // Actions\DeleteAction::make(),
            // Actions\ForceDeleteAction::make(),
            // Actions\RestoreAction::make(),
        ];
    }
}
