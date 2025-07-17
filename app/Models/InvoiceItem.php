<?php

namespace App\Models;

use App\Casts\AsMoney;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Models\InteracstsWithModelCaching;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use InteracstsWithModelCaching;
    use SoftDeletes;

    public $table = 'invoice_item';

    public $fillable = ['item_id', 'invoice_id', 'quantity', 'item_price'];

    public $casts = [
        'item_price' => AsMoney::class, // Cast item price to money format
    ];

    public $incrementing = true;

    public static function boot(): void
    {
        parent::boot();

        static::saved(function (self $invoice_item) {
            $invoice_item->invoice->touch();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
