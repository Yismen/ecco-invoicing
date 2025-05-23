<?php

use App\Models\Campaign;

it('save correct fields', function () {
    $data = Campaign::factory()->make();

    Campaign::create($data->toArray());

    $this->assertDatabaseHas(Campaign::class, $data->only([
        'name',
        'agent_id',
    ]));
});

it('belongs to an agent', function () {
    $data = Campaign::factory()
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
    $data = Campaign::factory()
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

it('has many invoices', function () {
    $data = Campaign::factory()
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
