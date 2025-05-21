<?php

namespace App\Filament\Invoicing\Resources\ParentClientResource\Pages;

use App\Filament\Invoicing\Resources\ParentClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParentClient extends EditRecord
{
    protected static string $resource = ParentClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
