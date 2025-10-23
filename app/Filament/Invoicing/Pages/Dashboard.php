<?php

namespace App\Filament\Invoicing\Pages;

use App\Models\Client;
use Filament\Forms\Form;
use App\Services\ModelListService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Invoicing\Widgets\MonthlyIncomes;
use Filament\Pages\Dashboard\Actions\FilterAction;
use App\Filament\Invoicing\Widgets\IncomeByProject;
use App\Filament\Invoicing\Widgets\InvoicesSummary;
use App\Filament\Invoicing\Widgets\OutstandingInvoices;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->slideOver(false)
                ->form([

                    DatePicker::make('startDate')
                        ->default(now()->subMonths(6)->startOfMonth())
                        ->maxDate(now()->endOfMonth()),
                    DatePicker::make('endDate')
                        ->default(now()->endOfMonth())
                        ->maxDate(now()->endOfMonth()),
                    Select::make('client')
                        ->label('Client')
                        ->options(
                            ModelListService::get(
                                model: Client::query(),
                                key_field: 'id',
                                value_field: 'name'
                            )
                        )
                        ->multiple()
                        ->searchable()
                        ->preload(),
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
                ]),
        ];
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
