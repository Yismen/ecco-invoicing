<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;
    use \App\Traits\Models\InteractsWithSpatieActivitylog;

    /** @use HasFactory<\Database\Factories\AgentFactory> */
    use HasFactory;

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'name',
        'project_id',
        'phone',
        'email',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
