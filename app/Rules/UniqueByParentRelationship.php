<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueByParentRelationship implements ValidationRule
{
    public function __construct(
        protected string|Builder $table,
        protected string $uniqueField,
        protected string $parentField,
        protected string $parentId,
        protected Model|null $recordToIgnore = null,
    ) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $record_exists = DB::table($this->getTable())
            ->where($this->uniqueField, 'like', $value)
            ->where($this->parentField, $this->parentId)
            ->when($this->recordToIgnore, function ($query) {
                return $query
                    ->where('id', '!=', $this->recordToIgnore->id);
            })
            ->exists();

        if ($record_exists) {
            $fail("The name {$value} already exits for this {$this->parentField}.");
        }
    }

    public function getTable(): string
    {
        $table = $this->table;

        if ($table instanceof Builder) {
            return $table->getModel()->getTable();
        }

        if (! str_contains($table, '\\') || ! class_exists($table)) {
            return $table;
        }

        if (is_subclass_of($table, Model::class)) {
            $model = new $table;

            if (str_contains($model->getTable(), '.')) {
                return $table;
            }

            return implode('.', array_map(function (string $part) {
                return trim($part, '.');
            }, array_filter([$model->getConnectionName(), $model->getTable()])));
        }

        return $table;
    }
}
