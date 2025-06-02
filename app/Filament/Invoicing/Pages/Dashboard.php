<?php

namespace App\Filament\Invoicing\Pages;

use Filament\Pages\Page;
use App\Filament\Invoicing\Widgets\MonthlyIncomes;
use App\Filament\Invoicing\Widgets\IncomeByProject;
use App\Filament\Invoicing\Widgets\InvoicesSummary;
use App\Filament\Invoicing\Widgets\OutstandingInvoices;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.invoicing.pages.dashboard';

    protected static ?int $navigationSort = -1;


    public function getHeaderWidgets(): array
    {
        return [
            // \App\Filament\Invoicing\Widgets\InvoicesOverview::class,
            // \App\Filament\Invoicing\Widgets\RecentInvoices::class,
            // \App\Filament\Invoicing\Widgets\RecentPayments::class,
           InvoicesSummary::class,
           MonthlyIncomes::class,
           IncomeByProject::class,
           OutstandingInvoices::class
        ];
    }

}
