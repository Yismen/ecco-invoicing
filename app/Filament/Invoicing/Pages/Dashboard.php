<?php

namespace App\Filament\Invoicing\Pages;

use Filament\Forms\Form;
use App\Services\ModelListService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Invoicing\Widgets\MonthlyIncomes;
use App\Filament\Invoicing\Widgets\IncomeByProject;
use App\Filament\Invoicing\Widgets\InvoicesSummary;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use App\Filament\Invoicing\Widgets\OutstandingInvoices;

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
                    ->multiple()
                    ->options(function () {
                        return ModelListService::get(
                            model: \App\Models\Project::query(),
                            key_field: 'id',
                            value_field: 'name'
                        );

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
