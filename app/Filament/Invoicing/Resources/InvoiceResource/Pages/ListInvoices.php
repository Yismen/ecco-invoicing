<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\Invoice;
use App\Enums\InvoiceStatuses;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Invoicing\Resources\InvoiceResource;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('7xl')
                // ->label('Create Invoice')
                // ->url(InvoiceResource::getUrl('create'))
                // ->openUrlInNewTab()
                ,
        ];
    }

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    // public function getTabs(): array
    // {
    //     $filters = [];
    //     $filters['all'] = Tab::make();

    //     foreach (InvoiceStatuses::cases() as $status) {
    //         $filters[$status->name] = Tab::make()
    //             ->badge(Invoice::query()->where('status', $status)->count())
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
    //     }

    //     return $filters;
    // }
}
