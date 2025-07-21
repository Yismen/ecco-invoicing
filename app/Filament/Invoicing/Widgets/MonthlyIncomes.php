<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyIncomes extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = '600s';

    protected static ?string $heading = 'Monthly Incomes';

    protected function getData(): array
    {
        $data = Trend::query(
            Invoice::query()
                ->when($this->filters['project'] ?? null, function ($query) {
                    $query->whereIn('project_id', (array)$this->filters['project']);
                })
        )
        ->between(
            start: $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : now()->subMonths(5),
            end: $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : now()->endOfMonth(),
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
