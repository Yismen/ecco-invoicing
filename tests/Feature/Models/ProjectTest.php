<?php

use App\Models\Project;

it('save correct fields', function () {
    $data = Project::factory()->make();

    Project::create($data->toArray());

    $this->assertDatabaseHas(Project::class, $data->only([
        'name',
        'agent_id',
    ]));
});

it('belongs to an agent', function () {
    $data = Project::factory()
        ->forAgent()
        ->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->agent()
    );

    $this->assertInstanceOf(
        \App\Models\Agent::class,
        $data->agent
    );
});

it('has many items', function () {
    $data = Project::factory()
        ->hasItems()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->items()
    );

    $this->assertInstanceOf(
        \App\Models\Item::class,
        $data->items->first()
    );
});
