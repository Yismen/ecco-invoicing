<?php

namespace App\Traits;

trait HasDefaultPolling
{
    protected static ?string $pollingInterval = '600s';

    protected function getPollingInterval(): ?string
    {
        return static::$pollingInterval;
    }
}
