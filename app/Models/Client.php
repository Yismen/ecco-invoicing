<?php

namespace App\Models;

use App\Traits\Models\HasNamePrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;

    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasNamePrefix;

    protected $fillable = [
        'name',
        'address',
        'tax_rate',
        'invoice_template',
        'invoice_notes',
        'invoice_terms',
        'invoice_net_days',
    ];

    protected $appends = [
        'invoice_prefix'
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
