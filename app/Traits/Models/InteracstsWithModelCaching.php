<?php

namespace App\Traits\Models;

use Illuminate\Support\Facades\Cache;

trait InteracstsWithModelCaching
{
    protected static function booted()
    {
        parent::booted();

        static::saved(function ($user) {
            Cache::flush();
        });

        static::deleted(function ($user) {
            Cache::flush();
        });
    }
}
