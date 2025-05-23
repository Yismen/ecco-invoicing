<?php

use App\Models\Client;
use App\Models\Project;

it('save correct fields', function () {
    $data = Project::factory()->make();

    Project::create($data->toArray());

    $this->assertDatabaseHas(Project::class, $data->only([
        'name',
        'client_id',
        'address',
        'phone',
        'email',
        'tax_rate',
        'invoice_notes',
        'invoice_terms',
        'invoice_net_days',
    ]));
});

it('belongs to client', function () {
    $data = Project::factory()
        ->forClient()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->client()
    );

    $this->assertInstanceOf(
        Client::class,
        $data->client
    );
});

it('has many agents', function () {
    $data = Project::factory()
        ->hasAgents()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->agents()
    );

    $this->assertInstanceOf(
        \App\Models\Agent::class,
        $data->agents->first()
    );
});

it('has many invoices', function () {
    $data = Project::factory()
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

it('gather invoice prefix name', function() {
    $data = Project::factory()->create(['name' => 'Some random name']);

    expect($data->invoice_prefix)
        ->toBe('SOMERN');
});
