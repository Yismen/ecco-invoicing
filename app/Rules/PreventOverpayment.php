<?php

namespace App\Rules;

use Closure;
use App\Models\Invoice;
use Illuminate\Contracts\Validation\Rule;

class PreventOverpayment implements Rule
{
    protected int $invoiceId;

    public function __construct(int|Closure $invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute  The field name (e.g. 'amount')
     * @param  mixed   $value      The attempted payment amount
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $invoice = Invoice::with('payments')->findOrFail($this->invoiceId);
        $paidSoFar = $invoice->payments->sum('amount');

        // Allow only if new total ≤ invoice total
        return ($paidSoFar + $value) <= $invoice->total_amount;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The payment would exceed the invoice total.';
    }
}
