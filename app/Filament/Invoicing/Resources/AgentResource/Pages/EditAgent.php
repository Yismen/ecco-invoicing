<?php

namespace App\Filament\Invoicing\Resources\AgentResource\Pages;

use App\Filament\Invoicing\Resources\AgentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgent extends EditRecord
{
    protected static string $resource = AgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
