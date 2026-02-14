<?php

namespace App\Filament\Invoicing\Widgets;

use App\DTOs\InvoicingDashboardFilterDTO;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyIncomes extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = '600s';

    protected ?string $heading = 'Monthly Incomes';

    protected function getData(): array
    {
        $filters = new InvoicingDashboardFilterDTO($this->pageFilters);

        $data = Trend::query(
            Invoice::query()
                ->when(
                    $filters->client,
                    function ($query) use ($filters) {
                        $query->whereHas(
                            'project',
                            function ($projectQuery) use ($filters) {
                                $projectQuery->whereIn('client_id', $filters->client);
                            }
                        );
                    }
                )
                ->when($filters->project, fn ($query) => $query->whereIn('project_id', $filters->project))
        )
            ->between(
                start: isset($this->pageFilters['startDate']) && $this->pageFilters['startDate'] ? Carbon::parse($this->pageFilters['startDate']) : now()->startOfMonth()->subMonths(5),
                end: isset($this->pageFilters['endDate']) && $this->pageFilters['endDate'] ? Carbon::parse($this->pageFilters['endDate']) : now()->endOfMonth(),
            )
            ->perMonth()
            ->dateColumn('date')
            ->dateAlias('Month')
            ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Incomes',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate / 100),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
