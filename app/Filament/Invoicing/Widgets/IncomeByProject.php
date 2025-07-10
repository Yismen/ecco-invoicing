<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use App\Services\InvoiceQueryForFilters;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class IncomeByProject extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Projects Income';

    protected function getData(): array
    {
        $data = InvoiceQueryForFilters::applyFilters(
            Invoice::query(),
            $this->filters
        )
            ->select('project_id')
            ->selectRaw('SUM(total_amount) as aggregate')
            ->groupBy('project_id')
            ->with('project')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Project Incomes',
                    'data' => $data->map(fn ($value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn ($value) => str($value->project->name)->limit(30) ?? 'Unknown Project'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
