<?php

namespace App\Services;

use App\DTOs\InvoicingDashboardFilterDTO;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class InvoiceQueryService
{
    protected array $filters;

    /**
     * InvoiceQueryService constructor.
     */
    public function __construct(InvoicingDashboardFilterDTO $filters)
    {
        $this->filters = [
            'startDate' => $filters->startDate,
            'endDate' => $filters->endDate,
            'project' => $filters->project,
        ];
    }

    public function getFilteredQuery(): Builder
    {
        return Invoice::query()
            ->when($this->filters['project'] ?? null, function ($query) {
                $query->whereIn('project_id', (array) $this->filters['project']);
            })
            ->when(
                $this->filters['startDate'] ?? null,
                function ($query) {
                    $query->whereDate('date', '>=', $this->filters['startDate']);
                }
            )
            ->when($this->filters['endDate'] ?? null, function ($query) {
                $query->whereDate('date', '<=', $this->filters['endDate']);
            });
    }
}
