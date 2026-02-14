<?php

namespace App\Models;

use App\Traits\Models\HasNamePrefix;
use App\Traits\Models\InteracstsWithModelCaching;
use App\Traits\Models\InteractsWithSpatieActivitylog;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use HasNamePrefix;
    use InteracstsWithModelCaching;
    use InteractsWithSpatieActivitylog;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'client_id',
        'address',
        'phone',
        'email',
        'tax_rate',
        'invoice_notes',
        'invoice_terms',
        'invoice_net_days',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
