<?php

namespace App\Services;

use Flowframe\Trend\Trend;

class TrendService extends Trend
{
    public function dateColumn(string $column, ?string $alias = null): self
    {
        if (! $alias) {
            throw new \Exception('Alias is required', 400);
        }
        if (str($alias)->lower()->toString() === 'date') {
            throw new \Exception("Alias can't be named as date. Use any other custom name!", 400);
        }

        $this->dateColumn = $column;
        $this->dateAlias = $alias;

        return $this;
    }
}
