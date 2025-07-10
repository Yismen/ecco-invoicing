<?php

namespace App\Filament\Invoicing\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatuses;
use App\Filament\Invoicing\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $filters = [];
        $filters['all'] = Tab::make();

        foreach (InvoiceStatuses::cases() as $status) {
            $filters[$status->name] = Tab::make()
                ->badge(Invoice::query()->where('status', $status)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $filters;
    }
}
