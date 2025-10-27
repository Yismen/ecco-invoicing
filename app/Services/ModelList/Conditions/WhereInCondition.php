<?php

namespace App\Services\ModelList\Conditions;

class WhereInCondition
{
    public function __construct(
        public string $field,
        public array $values
    )
    {
        //
    }
}
