<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use App\Services\InvoiceQueryForFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class InvoicesSummary extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        return [

            Stat::make('Total Invoiced', Number::currency(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query(),
                    $this->filters
                )
                    ->sum('total_amount') / 100,
                'USD'
            ))
                ->color(Color::Blue)
                ->icon('heroicon-o-document-text')
                ->description('Total amount for all invoices'),

            Stat::make('Total Paid', Number::currency(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query(),
                    $this->filters
                )
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('total_paid') / 100,
                'USD'
            ))
                ->color(Color::Green)
                ->icon('heroicon-o-document-text')
                ->description('Total amount for paid invoices'),

            Stat::make('Pending Invoices', Number::currency(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query()
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Paid)
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled),
                    $this->filters
                )
                    ->sum('balance_pending') / 100,
                'USD'
            ))
                ->color(Color::Red)
                ->icon('heroicon-o-document-text')
                ->description('Total amount pending to be paid'),
        ];
    }
}
