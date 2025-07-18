<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class InvoiceQueryService
{
    protected array $filters;
    /**
     * InvoiceQueryService constructor.
     *
     * @param array $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = array_merge([
            'startDate' => null,
            'endDate' => null,
            'project' => null,
        ], $filters);
    }

    public function getFilteredQuery(): Builder
    {
        return Invoice::query()
            ->when($this->filters['project'] ?? null, function ($query)  {
                $query->whereIn('project_id', (array)$this->filters['project']);
            })
            ->when(
                $this->filters['startDate'] ?? null,
                function ($query)  {
                    $query->whereDate('date', '>=', $this->filters['startDate']);
                }
            )
            ->when($this->filters['endDate'] ?? null, function ($query)  {
                $query->whereDate('date', '<=', $this->filters['endDate']);
            });
    }
}
