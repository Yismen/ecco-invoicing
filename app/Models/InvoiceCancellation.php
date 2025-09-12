<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceCancellation extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceCancellationFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'cancellation_date',
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
            $invoiceCancellation->invoice->touch();
        });

    }
}
