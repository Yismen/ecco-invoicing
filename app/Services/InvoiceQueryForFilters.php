<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class InvoiceQueryForFilters
{
    public static function applyFilters(Builder $query, $filters): Builder
    {
        return $query
            ->when($filters['project'] ?? null, function ($query) use ($filters) {
                $query->whereIn('project_id', (array)$filters['project']);
            })
            ->when(
                $filters['startDate'] ?? null,
                function ($query) use ($filters) {
                    $query->whereDate('date', '>=', $filters['startDate']);
                },
                function ($query) {
                    $query->whereDate('date', '>=', now()->startOfMonth());
                }
            )
            ->when($filters['endDate'] ?? null, function ($query) use ($filters) {
                $query->whereDate('date', '<=', $filters['endDate']);
            });
    }
}
