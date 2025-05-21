<?php

namespace App\Models;

use App\Traits\Models\HasNamePrefix;
use App\Traits\Models\InteracstsWithModelCaching;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentClient extends Model
{
    /** @use HasFactory<\Database\Factories\ParentClientFactory> */
    use HasFactory;
    use HasNamePrefix;
    use SoftDeletes;
    use InteracstsWithModelCaching;

    protected $fillable = [
        'name',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
