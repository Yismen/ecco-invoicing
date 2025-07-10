<?php

namespace App\Filament\Invoicing\Pages;

use App\Filament\Invoicing\Widgets\IncomeByProject;
use App\Filament\Invoicing\Widgets\InvoicesSummary;
use App\Filament\Invoicing\Widgets\MonthlyIncomes;
use App\Filament\Invoicing\Widgets\OutstandingInvoices;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('startDate')
                    ->default(now()->subMonths(6)->startOfMonth())
                    ->maxDate(now()->endOfMonth()),
                DatePicker::make('endDate')
                    ->default(now()->endOfMonth())
                    ->maxDate(now()->endOfMonth()),
                Select::make('project')
                    ->label('Project Name')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return \App\Models\Project::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');

                    })
                    ->placeholder('Enter project name'),
            ]);
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            InvoicesSummary::class,
            MonthlyIncomes::class,
            IncomeByProject::class,
            OutstandingInvoices::class,
        ];
    }
}
