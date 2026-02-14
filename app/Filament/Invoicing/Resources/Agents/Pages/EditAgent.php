<?php

namespace App\Filament\Invoicing\Resources\Agents\Pages;

use App\Filament\Invoicing\Resources\Agents\AgentResource;
use Filament\Actions;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAgent extends EditRecord
{
    protected static string $resource = AgentResource::class;

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
