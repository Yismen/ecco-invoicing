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
    public static function get(
        string|Builder $model,
        string $key_field = 'id',
        string $value_field = 'name',
        array $conditions = []
    ): array {
        if (filled($conditions) && isset($conditions[0]) && ! is_array($conditions[0])) {
            throw new \InvalidArgumentException('Conditions must be an array of arrays.');
        }

        $model = $model instanceof Builder ? $model : $model::query();

        return Cache::rememberForever(
            self::getCacheKey($model, $conditions),
            fn() => self::getResults($model, $key_field, $value_field, $conditions)
        );
    }

    private static function getResults( $model, $key_field, $value_field, $conditions) {
        $model = $model
            ->orderBy($value_field);

        foreach ($conditions as $key => $condition) {
            \Illuminate\Support\Facades\Log::info($key == 'in');
            if ($key == 'in') {
                $model->whereIn($condition[0], $condition[1]);
            } else {
                $model->where($condition);
            }
        }

        return $model->pluck($value_field, $key_field)
            ->toArray();
    }

    private static function getCacheKey($model, array $condition): string
    {
        $conditionsKey = empty($condition) ? '' : json_encode($condition);

        $key = implode('_', [
            'model_list',
            str(get_class($model->getModel()))->replace('\\', '')->snake(),
            $conditionsKey,
        ]);

        return $key;
    }
}
