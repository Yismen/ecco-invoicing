<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validate that a given field is unique within the scope of a parent relationship.
 * For example, ensure that an agent's name is unique within a specific project.
 *
 * Usage: new UniqueByParentRelationship(
 *    table: Agent::class || 'agents' || Agent::query(),
 *    uniqueField: 'name',
 *    parentField: 'project_id',
 *    parentId: $projectId,
 *    recordToIgnore: $record, // optional
 * )
 */
class UniqueByParentRelationship implements ValidationRule
{
    public function __construct(
        protected string|Builder $table,
        protected string $uniqueField,
        protected string $parentField,
        protected null|string|int $parentId,
        protected ?Model $recordToIgnore = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=):PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($this->parentId)) {
            $fail("The parent ID for {$this->parentField} is not set.");

            return;
        }

        $recordFound = DB::table($this->getTable())
            ->where($this->uniqueField, 'like', $value)
            ->where($this->parentField, $this->parentId)
            ->first();

        if ($this->recordToIgnore && $recordFound && $recordFound->id === $this->recordToIgnore->id) {
            $recordFound = null;
        }

        if ($recordFound) {
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
