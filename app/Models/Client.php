<?php

namespace App\Models;

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

    public function getinvoicePrefixAttribute()
    {

        $name = str($this->attributes['name'])->upper()->replace(['-', '_', '/', ',', '\''], ' ');

        $name = preg_replace('/s+/', ' ', $name);

        $name = explode(" ", $name, 3);

        return $name[0] . (str($name[1] ?? '')->substr(0, 1)) . (str($name[2] ?? '')->substr(0, 1));
    }
}
