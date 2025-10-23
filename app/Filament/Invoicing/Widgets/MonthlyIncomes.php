<?php

namespace App\Filament\Invoicing\Widgets;

use Carbon\Carbon;
use App\Models\Invoice;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use App\DTOs\InvoicingDashboardFilterDTO;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyIncomes extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = '600s';

    protected static ?string $heading = 'Monthly Incomes';

    protected function getData(): array
    {
        $filters = new InvoicingDashboardFilterDTO($this->filters);

        $data = Trend::query(
            Invoice::query()
                ->when(
                    $filters->client,
                    function ($query) use ($filters) {
                        $query->whereHas(
                            'project',
                            function($projectQuery) use ($filters) {
                                $projectQuery->whereIn('client_id', $filters->client);
                            }
                        );
                    }
                )
                ->when($filters->project, fn ($query) => $query->whereIn('project_id', $filters->project))
        )
            ->between(
                start: isset($this->filters['startDate']) && $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : now()->startOfMonth()->subMonths(5),
                end: isset($this->filters['endDate']) && $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : now()->endOfMonth(),
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
