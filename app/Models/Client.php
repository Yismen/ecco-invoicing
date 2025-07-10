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

    use HasNamePrefix;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'invoice_template',
        'template_date_field_name',
        'template_project_field_name',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // public function campaigns(): HasMany
    // {
    //     return $this->hasMany(Campaign::class);
    // }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Project::class);
    }
}
