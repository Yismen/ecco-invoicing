<?php

namespace App\Services;

use App\DTOs\InvoicingDashboardFilterDTO;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class InvoiceQueryService
{
    /**
     * InvoiceQueryService constructor.
     */
    public function __construct(public InvoicingDashboardFilterDTO $filters) {}

    public function getFilteredQuery(): Builder
    {
        return Invoice::query()
            ->when($this->filters->client ?? null, function ($query) {
                $query->whereHas('project', fn ($projectQury) => $projectQury->where('client_id', $this->filters->client));
            })
            ->when($this->filters->project ?? null, function ($query) {
                $query->whereIn('project_id', (array) $this->filters->project);
            })
            ->when(
                $this->filters->startDate ?? null,
                function ($query) {
                    $query->whereDate('date', '>=', $this->filters->startDate);
                }
            )
            ->when($this->filters->endDate ?? null, function ($query) {
                $query->whereDate('date', '<=', $this->filters->endDate);
            });
    }
}
