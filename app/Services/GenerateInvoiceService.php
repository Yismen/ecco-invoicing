<?php

namespace App\Services;

use App\Models\Invoice as ModelsInvoice;
use LaravelDaily\Invoices\Invoice;

class GenerateInvoiceService extends Invoice
{
    public array $extra_data;

    public ModelsInvoice $model;

    public function __construct($name = '')
    {
        parent::__construct($name);

        $this->extra_data = [];
    }

    public function extraData(array $extra_data): self
    {
        $this->extra_data = $extra_data;

        return $this;
    }

    public function model(ModelsInvoice $model): self
    {
        $this->model = $model;

        return $this;
    }
}
