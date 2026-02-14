<?php

namespace App\Filament\Invoicing\Resources\Campaigns\Pages;

use App\Filament\Invoicing\Resources\Campaigns\CampaignResource;
use Filament\Actions;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

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
