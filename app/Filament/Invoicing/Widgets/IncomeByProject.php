<?php

namespace App\Filament\Invoicing\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class IncomeByProject extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Projects Income';

    protected function getData(): array
    {
        $data = Invoice::query()
            ->select('project_id')
            ->selectRaw('SUM(total_amount) as aggregate')
            ->groupBy('project_id')
            ->with('project')
            ->when($this->filters['project'] ?? null, function ($query) {
                $query->where('project_id', $this->filters['project']);
            })
            ->when($this->filters['startDate'] ?? null, function ($query) {
                $query->whereDate('date', '>=', $this->filters['startDate']);
            })
            ->when($this->filters['endDate'] ?? null, function ($query) {
                $query->whereDate('date', '<=', $this->filters['endDate']);
            })
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
