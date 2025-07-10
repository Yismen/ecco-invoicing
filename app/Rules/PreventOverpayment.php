<?php

namespace App\Rules;

use App\Models\Invoice;
use App\Models\Payment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PreventOverpayment implements ValidationRule
{
    public function __construct(protected Invoice|Payment $model) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $paidSoFar = 0;
        $total = 0;

        if ($this->model instanceof Invoice) {
            $paidSoFar = $this->model->payments()->sum('amount');
            $total = $this->model->total_amount ?? 0;
        }

        if ($this->model instanceof Payment) {
            $paidSoFar = $this->model->invoice->payments()->sum('amount') - $this->model->amount;
            $total = $this->model->invoice->total_amount ?? 0;
        }

        if ($paidSoFar + $value > $total) {
            $fail("The payment amount exceeds the invoice pending of {$total}.");
        }

        if ($value <= 0) {
            $fail('The payment amount must be greater than zero.');
        }

    }
}
