<?php

namespace App\Models;

use App\Exceptions\PreventCancellingInvoiceWithPaymentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceCancellation extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;
    use SoftDeletes;
    /** @use HasFactory<\Database\Factories\InvoiceCancellationFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'date',
        'comments',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public static function boot(): void
    {
        parent::boot();

        static::saved(function (self $invoiceCancellation) {
            throw_if(
                $invoiceCancellation->invoice->payments()->sum('amount'),
                new PreventCancellingInvoiceWithPaymentException('Invoice has payments already!')
            );

            $invoiceCancellation->invoice->touch();
        });

        static::deleted(function (self $invoiceCancellation) {
            $invoiceCancellation->invoice->touch();
        });
    }
}
