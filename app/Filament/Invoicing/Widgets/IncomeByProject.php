<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Number;

class IncomeByProject extends ChartWidget
{
    protected static ?string $heading = 'Projects Income';

    protected function getData(): array
    {
        $data = Invoice::query()
            ->select('project_id')
            ->selectRaw('SUM(total_amount) as aggregate')
            ->groupBy('project_id')
            ->with('project')
            ->whereBetween('date', [
                now()->subMonths(6)->startOfMonth(),
                now(),
            ])
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Project Incomes',
                    'data' => $data->map(fn ($value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn ($value) => $value->project->name ?? 'Unknown Project'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
