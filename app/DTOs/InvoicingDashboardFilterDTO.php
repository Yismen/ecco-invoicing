<?php

namespace App\DTOs;

class InvoicingDashboardFilterDTO
{
    public ?string $startDate;
    public ?string $endDate;
    public array $project;
    public array $client;

    public function __construct( ?array $filters)
    {
        $this->startDate = $filters['startDate'] ?? null;
        $this->endDate = $filters['endDate'] ?? null;
        $this->project = $filters['project'] ?? [];
        $this->client = $filters['client'] ?? [];
    }
}
