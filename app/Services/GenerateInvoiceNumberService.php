<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class GenerateInvoiceNumberService
{
    public static function generate(Project $project): string
    {
         return Cache::rememberForever(
            'invoice_number_for_project_' . $project->id,
            function () use ($project) {
                $project->load('client');

                return join('-', [
                    config('app.company.short_name'),
                    $project->client->invoiceNamePrefix(3),
                    $project->invoiceNamePrefix(),
                    str($project->invoices()->count() + 1)->padLeft(config('app.company.invoice_length', 4), 0)
                ]);

            }
        );
    }
}
