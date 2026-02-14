<?php

namespace App\Filament\Invoicing\Resources\Campaigns\Pages;

use App\Filament\Invoicing\Resources\Campaigns\CampaignResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
