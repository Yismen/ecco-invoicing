<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;

    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'campaign_id',
        'price',
        'description',
        'image',
        'category',
        'brand',
        'sku',
        'barcode',
    ];

    protected $casts = [
        'price' => \App\Casts\AsMoney::class,
    ];

    public function name(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => trim($value),
        );
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class);
    }
}
