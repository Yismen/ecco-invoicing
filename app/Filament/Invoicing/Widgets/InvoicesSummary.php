<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class InvoicesSummary extends BaseWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make('Total Incomes',  Number::currency(
                Invoice::query()
                    ->sum('total_amount'),
                'USD'
            ))
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->description('Total amount for all invoices'),

            Stat::make('Outstanding Invoices',  Number::currency(
                Invoice::query()
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Paid)
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('balance_pending'),
                        'USD'
            ))
                ->color('danger')
                ->icon('heroicon-o-document-text')
                ->description('Total amount for outstanding invoices'),

            Stat::make('Total Paid',  Number::currency(
                Invoice::query()
                    ->where('status', '!=', \App\Enums\InvoiceStatuses::Cancelled)
                    ->sum('total_paid'),
                'USD'
            ))
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->description('Total amount for paid invoices'),
        ];
    }
}
