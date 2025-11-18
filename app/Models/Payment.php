<?php

namespace App\Models;

use App\Casts\AsMoney;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exceptions\InvoiceOverpaymentException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;
    use \App\Traits\Models\InteractsWithSpatieActivitylog;

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
        'amount' => AsMoney::class,
        'images' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($payment) {
            $invoice = $payment->load('invoice')->invoice;
            $totalPaid = $invoice->payments()
                ->where('id', '!=', $payment->id) // Exclude current payment if updating
                ->sum('amount') / 100; // Convert cents to dollars

            $attempt = $totalPaid + $payment->amount;

            if ($attempt > $invoice->total_amount) {
                throw new InvoiceOverpaymentException('Payment exceeds invoice total');
            }
        });

        static::saved(function ($payment) {
            $payment->invoice->touch();
        });

        static::deleting(function ($payment) {
            $payment->invoice->touch();
        });

        static::softDeleted(function ($payment) {
            $payment->invoice->touch();
        });

        static::restored(function ($payment) {
            $payment->invoice->touch();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
