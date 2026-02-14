<?php

namespace App\Filament\Invoicing\Resources\Invoices\Pages;

use App\Enums\InvoiceStatuses;
use App\Filament\Invoicing\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('7xl')
            // ->label('Create Invoice')
            // ->url(InvoiceResource::getUrl('create'))
            // ->openUrlInNewTab()
            ,
        ];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
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
