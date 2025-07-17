<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use App\Services\InvoiceQueryForFilters;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class InvoicesSummary extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        return [

            Stat::make('Total Incomes', Number::currency(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query(),
                    $this->filters
                )
                    ->sum('total_amount') / 100,
                'USD'
            ))
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->description('Total amount for all invoices'),

            Stat::make('Outstanding Invoices', Number::currency(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query(),
                    $this->filters
                )
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Paid)
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('balance_pending') / 100,
                'USD'
            ))
                ->color('danger')
                ->icon('heroicon-o-document-text')
                ->description('Total amount for outstanding invoices'),

            Stat::make('Total Paid', Number::currency(
                InvoiceQueryForFilters::applyFilters(
                    Invoice::query(),
                    $this->filters
                )
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('total_paid') / 100,
                'USD'
            ))
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->description('Total amount for paid invoices'),
        ];
    }
}
