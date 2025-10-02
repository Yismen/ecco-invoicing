<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class GenerateInvoiceNumberService
{
    public static function generate(Project $project): string
    {
        return Cache::rememberForever(
            'invoice_number_for_project_'.$project->id,
            function () use ($project) {
                $project->load('client');

                return implode('-', [
                    config('app.company.short_name'),
                    $project->client->invoiceNamePrefix(3),
                    $project->invoiceNamePrefix(),
                    str(
                        $project
                            ->invoices()
                            ->withTrashed()
                            ->count() + 1
                    )->padLeft(
                        length: config('app.company.invoice_length', 4),
                        pad: 0
                    ),
                ]);

            }
        );
    }
}
