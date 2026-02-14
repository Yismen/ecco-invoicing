<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\GenerateInvoiceService;

class GenerateInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice, GenerateInvoiceService $invoiceService)
    {

        return $invoiceService
            ->generate($invoice)
            ->toFile();
    }
}
