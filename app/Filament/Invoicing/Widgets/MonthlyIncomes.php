<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use App\Services\InvoiceQueryForFilters;
use App\Services\TrendService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyIncomes extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Incomes';

    protected function getData(): array
    {
        $data = TrendService::query(
            InvoiceQueryForFilters::applyFilters(
                Invoice::query(),
                $this->filters
            )
        )
        ->between(
            start: $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : now()->startOfMonth(),
            end: $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : now()->endOfMonth(),
        )
        ->perMonth()
        ->dateColumn('date', 'Month')
        ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Incomes',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
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
