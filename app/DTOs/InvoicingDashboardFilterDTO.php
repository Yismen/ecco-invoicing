<?php

namespace App\DTOs;

class InvoicingDashboardFilterDTO
{

    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?array $project = null
    )
    {}
}
