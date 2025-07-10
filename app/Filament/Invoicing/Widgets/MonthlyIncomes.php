<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use App\Services\InvoiceQueryForFilters;
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
        $data = Trend::query(
            InvoiceQueryForFilters::applyFilters(
                Invoice::query(),
                $this->filters
            )
        )
            ->dateColumn('date')
            ->between(
                start: Carbon::parse($this->filters['startDate']) ?? now()->subMonths(6)->startOfMonth(),
                end: Carbon::parse($this->filters['endDate']) ?? now()->endOfMonth(),
            )
            ->perMonth()
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
