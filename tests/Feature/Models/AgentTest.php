<?php

use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;

it('save correct fields', function () {
    $data = Agent::factory()->make();

    Agent::create($data->toArray());

    $this->assertDatabaseHas(Agent::class, $data->only([
        'name',
        'project_id',
        'phone',
        'email',
    ]));
});

it('belongs to a project', function () {
    $data = Agent::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->project()
    );

    $this->assertInstanceOf(
        Project::class,
        $data->project
    );
});

it('has many campaigns', function () {
    $data = Agent::factory()
        ->hasCampaigns()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->campaigns()
    );
    $this->assertInstanceOf(
        \App\Models\Campaign::class,
        $data->campaigns->first()
    );
});

it('has many invoices', function () {
    $data = Agent::factory()
        ->hasInvoices()
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
