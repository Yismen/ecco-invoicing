<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ModelListService
{
    public static function get(
        string|Builder $model,
        string $key_field,
        string $value_field,
        array $conditions = []
    ): array {
        if (filled($conditions) && isset($conditions[0]) && ! is_array($conditions[0])) {
            throw new \InvalidArgumentException('Conditions must be an array of arrays.');
        }

        $model = $model instanceof Builder ? $model : $model::query();

        return Cache::rememberForever(self::getCacheKey($model, $conditions), function () use (
            $model,
            $key_field,
            $value_field,
            $conditions,
        ) {
            $model = $model
                ->orderBy($value_field);

            foreach ($conditions as $condition) {
                $model->where($condition);
            }

            return $model->pluck($value_field, $key_field)
                ->toArray();
        });
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
