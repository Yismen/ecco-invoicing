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
            ->logOnly(
                $this->getTrackableAttributes()
            )
            ->dontLogIfAttributesChangedOnly($this->getIgnoredChangedAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function getIgnoredChangedAttributes(): array
    {
        return static::$ignoreChangedAttributes ?? [];
    }

    protected function getTrackableAttributes(): array
    {
        $attributes = \array_keys($this->attributes);

        $attributes = \array_filter($attributes, function ($value) {
            return in_array(strtolower($value), static::$ignoreChangedAttributes) == false;
        });

        return \array_map(function ($value) {
            $split = \explode('_id', $value);
            return count($split) > 1 ?
                $split[0] . '.name' :
                $split[0];
        }, $attributes);
    }
}
