<?php

use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;

it('save correct fields', function () {
    $data = Agent::factory()->make();

    Agent::create($data->toArray());

    $this->assertDatabaseHas(Agent::class, $data->only([
        'name',
        'client_id',
        'phone',
        'email',
    ]));
});

it('belongs to a client', function () {
    $data = Agent::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->client()
    );

    $this->assertInstanceOf(
        Client::class,
        $data->client
    );
});

it('has many projects', function () {
    $data = Agent::factory()
        ->hasProjects(1)
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->projects()
    );
    $this->assertInstanceOf(
        \App\Models\Project::class,
        $data->projects->first()
    );
});

it('has many invoices', function () {
    $data = Agent::factory()
        ->hasInvoices(2)
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->invoices()
    );

    $this->assertInstanceOf(
        \App\Models\Invoice::class,
        $data->invoices->first()
    );
});
