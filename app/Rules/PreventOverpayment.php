<?php

namespace App\Rules;

use Closure;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;

class PreventOverpayment implements ValidationRule
{

    public function __construct(protected null|Invoice $invoice = null, protected null|Payment $payment = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $paidSoFar = $this->invoice->payments()->where('id', '!=', $this->payment?->id)->sum('amount');
        $attempt = $paidSoFar + $value;
        $amountPending = $this->invoice->balance_pending;

        if ($attempt > $amountPending) {
            $fail('The :attribute would exceed the invoice total pending of ' . $amountPending . '.');
        }
    }
}
