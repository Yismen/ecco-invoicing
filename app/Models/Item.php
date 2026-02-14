<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Traits\Models\InteracstsWithModelCaching;
use App\Traits\Models\InteractsWithSpatieActivitylog;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;
    use InteracstsWithModelCaching;

    use InteractsWithSpatieActivitylog;

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
        'price' => AsMoney::class,
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
