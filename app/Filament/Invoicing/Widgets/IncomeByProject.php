<?php

namespace App\Filament\Invoicing\Widgets;

use App\DTOs\InvoicingDashboardFilterDTO;
use App\Services\InvoiceQueryService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class IncomeByProject extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Projects Income';

    protected ?string $pollingInterval = '600s';

    protected function getData(): array
    {
        $service = new InvoiceQueryService(new InvoicingDashboardFilterDTO($this->pageFilters));

        $data = $service->getFilteredQuery()
            ->select('project_id')
            ->selectRaw('SUM(total_amount) as aggregate')
            ->groupBy('project_id')
            ->with('project')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Project Incomes',
                    'data' => $data->map(fn ($value) => $value->aggregate / 100), // Convert cents to dollars
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
