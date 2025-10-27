<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Service for retrieving lists of models with optional conditions and caching.
 *
 * Usage:
 * - Call ModelListService::get() with a model class or query builder, key and value fields, and optional conditions.
 * - Returns an associative array of key-value pairs from the model, cached for performance.
 *
 * Example:
 *   ModelListService::get(User::class, 'id', 'name', [['active', '=', 1]]);
 */
class ModelListService
{
    public static self $instance;
    public string|Builder $model;
    public string $key_field;
    public string $value_field;
    public array $conditions = [];

    public static function get(
        string|Builder $model,
        string $key_field = 'id',
        string $value_field = 'name',
        array $conditions = []
    ): array {

        if (filled($conditions) && isset($conditions[0]) && ! is_array($conditions[0])) {
            throw new \InvalidArgumentException('Conditions must be an array of arrays.');
        }

        self::$instance ??= new self();
        self::$instance->key_field = $key_field;
        self::$instance->value_field = $value_field;
        self::$instance->conditions = $conditions;
        self::$instance->model = $model instanceof Builder ? $model : $model::query();

        return Cache::rememberForever(
            self::getCacheKey(),
            fn() => self::getResults()
        );
    }

    private static function getResults() {
        $model = self::$instance->model
            ->orderBy(self::$instance->value_field);

        foreach (self::$instance->conditions as $key => $condition) {
            if ($key == 'in') {
                $model->whereIn($condition[0], $condition[1]);
            } else {
                $model->where($condition);
            }
        }

        return $model->pluck(self::$instance->value_field, self::$instance->key_field)
            ->toArray();
    }

    private static function getCacheKey(): string
    {
        $conditionsKey = empty(self::$instance->conditions) ? '' : json_encode(self::$instance->conditions);

        $key = implode('_', [
            'model_list',
            str(get_class(self::$instance->model->getModel()))->replace('\\', '')->snake(),
            $conditionsKey,
        ]);

        return $key;
    }
}
