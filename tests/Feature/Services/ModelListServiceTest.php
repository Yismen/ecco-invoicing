<?php

use App\Models\Project;
use App\Services\ModelListService;
use Illuminate\Support\Facades\Cache;

it('generates the correct key', function () {
    ModelListService::get(
        model: \App\Models\Project::class,
        key_field: 'id',
        value_field: 'name'
    );

    expect(Cache::has('model_list_app_models_project_'))
        ->toBeTrue();
});

it('generates the correct key when conditions are provided', function () {
    ModelListService::get(
        model: \App\Models\Project::class,
        key_field: 'id',
        value_field: 'name',
        conditions: ['status' => 'active']
    );

    expect(Cache::has('model_list_app_models_project_{"status":"active"}'))
        ->toBeTrue();
});


it('returns the correct values', function () {
    Project::factory()->count(3)->create();

    $service = ModelListService::get(
        model: \App\Models\Project::class,
        key_field: 'id',
        value_field: 'name'
    );

    $projects = Project::query()->orderBy('name')->pluck('name', 'id')->toArray();

    expect($projects)
        ->toBe($service);
});


it('returns the correct values with conditions', function () {
    Project::factory()->count(3)->create();
    Project::factory()->create(['name' => 'new condition']);

    $service = ModelListService::get(
        model: \App\Models\Project::class,
        key_field: 'id',
        value_field: 'name',
        conditions: [
            ['name' => 'new condition']
        ]
    );

    $projects = Project::query()->orderBy('name')->where('name', 'new condition')->pluck('name', 'id')->toArray();

    expect($projects)
        ->toBe($service);
});

it('throws an exception if conditions are not an associative array', function () {
    ModelListService::get(
        model: \App\Models\Project::query(),
        key_field: 'id',
        value_field: 'name',
        conditions: ['active'] // This is not an associative array
    );
})->throws(\InvalidArgumentException::class, 'Conditions must be an array of arrays.');
