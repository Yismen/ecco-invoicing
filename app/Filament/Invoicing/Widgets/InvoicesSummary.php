<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use App\Services\InvoiceQueryService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class InvoicesSummary extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = '600s';

    protected function getStats(): array
    {
        $service = new InvoiceQueryService($this->filters);

        return [

            Stat::make('Total Invoiced', Number::currency(
                $service->getFilteredQuery()
                    ->sum('total_amount') / 100
            ))
                ->color(Color::Blue)
                ->icon('heroicon-o-document-text')
                ->description('Total amount for all invoices'),

            Stat::make('Total Paid', Number::currency(
                $service->getFilteredQuery()
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('total_paid') / 100
            ))
                ->color(Color::Green)
                ->icon('heroicon-o-document-text')
                ->description('Total amount for paid invoices'),

            Stat::make('Pending Invoices', Number::currency(
                $service->getFilteredQuery()
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Paid)
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('balance_pending') / 100
            ))
                ->color(Color::Red)
                ->icon('heroicon-o-document-text')
                ->description('Total amount pending to be paid'),
        ];
    }
}
