<?php

namespace App\Filament\Invoicing\Resources\AgentResource\Pages;

use App\Filament\Invoicing\Resources\AgentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAgent extends ViewRecord
{
    protected static string $resource = AgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
