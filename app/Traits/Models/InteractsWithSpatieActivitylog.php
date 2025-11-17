<?php

namespace App\Traits\Models;

use Spatie\Activitylog\LogOptions;

trait InteractsWithSpatieActivitylog
{
    use \Spatie\Activitylog\Traits\LogsActivity;

    protected static $ignoreChangedAttributes = [
        'updated_at',
    ];

    protected static $recordEvents = [
        'updated',
        'deleted',
        'created',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontLogIfAttributesChangedOnly($this->getIgnoredChangedAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function getIgnoredChangedAttributes(): array
    {
        return static::$ignoreChangedAttributes ?? [];
    }
}
