<?php

namespace App\Models;

use App\Traits\Models\HasNamePrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;
    use \App\Traits\Models\InteractsWithSpatieActivitylog;

    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    use HasFactory;

    use HasNamePrefix;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'agent_id',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
