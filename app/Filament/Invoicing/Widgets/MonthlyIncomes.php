<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Number;

class MonthlyIncomes extends ChartWidget
{
    protected static ?string $heading = 'Monthly Incomes';

    protected function getData(): array
    {
        $data = Trend::model(Invoice::class)
            ->dateColumn('date')
            ->between(
                start: now()->subMonths(6)->startOfMonth(),
                end: now(),
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
