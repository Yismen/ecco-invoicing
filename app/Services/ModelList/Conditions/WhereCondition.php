<?php

namespace App\Services\ModelList\Conditions;

class WhereCondition
{
    public function __construct(
        public string $field,
        public $value,
        public string $operator = '=',
    ) {
        //
    }
}
