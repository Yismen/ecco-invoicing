<?php

use App\Models\Agent;
use App\Models\Client;
use App\Models\ParentClient;

it('save correct fields', function () {
    $data = Client::factory()->make();

    Client::create($data->toArray());

    $this->assertDatabaseHas(Client::class, $data->only([
        'name',
        'address',
        'tax_rate',
        'parent_client_id',
        'invoice_template',
        'invoice_notes',
        'invoice_terms',
        'invoice_net_days',
    ]));
});

it('has many agents', function () {
    $data = Client::factory()
        ->hasAgents()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->agents()
    );

    $this->assertInstanceOf(
        Agent::class,
        $data->agents->first()
    );
});

it('belongs to parent client', function () {
    $data = Client::factory()
        ->forParentClient()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->parentClient()
    );

    $this->assertInstanceOf(
        ParentClient::class,
        $data->parentClient->first()
    );
});

it('has many invoices', function () {
    $data = Client::factory()
        ->hasInvoices()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->invoices()
    );

    $this->assertInstanceOf(
        \App\Models\Invoice::class,
        $data->invoices()->first()
    );
});

it('gather invoice prefix name', function() {
    $data = Client::factory()->create(['name' => 'Some random name']);

    expect($data->invoice_prefix)
        ->toBe('SOMERN');
});

// it('has many payments', function () {
//     $data = Client::factory()
//         ->hasPayments()
//         ->create();

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\HasMany::class,
//         $data->payments()
//     );
//     $this->assertEquals(
//         $data->getForeignKeyName(),
//         $data->payments()->getForeignKeyName()
//     );
// });

// it('has many projects', function () {
//     $data = Client::factory()
//         ->hasProjects()
//         ->create();

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\HasMany::class,
//         $data->projects()
//     );
//     $this->assertEquals(
//         $data->getForeignKeyName(),
//         $data->projects()->getForeignKeyName()
//     );
// });
