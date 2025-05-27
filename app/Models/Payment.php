<?php

namespace App\Models;

use App\Enums\InvoiceStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;

    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'amount',
        'date',
        'reference',
        'images',
        'description',
    ];

    protected $casts = [
        'date' => 'datetime',
        'images' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($payment) {
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()
                ->where('id', '!=', $payment->id) // Exclude current payment if updating
                ->sum('amount');

            $attempt = $totalPaid + $payment->amount;

            if ($attempt > $invoice->total_amount) {
                throw new \Exception("Payment exceeds invoice total");
            }
        });

        static::saved(function($payment) {
            if ($payment->invoice->balance_pending > 0) {
                $payment->invoice->updateQuietly([
                    'status' => InvoiceStatuses::PartiallyPaid
                ]);
            }

            if ($payment->invoice->balance_pending == 0) {
                $payment->invoice->updateQuietly([
                    'status' => InvoiceStatuses::Paid
                ]);
            }
            // $payment->invoice->touch();

        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
