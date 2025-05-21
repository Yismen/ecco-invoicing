<?php

use App\Models\Client;
use App\Models\ParentClient;

it('save correct fields', function () {
    $data = ParentClient::factory()->make();

    ParentClient::create($data->toArray());

    $this->assertDatabaseHas(ParentClient::class, $data->only([
        'name',
    ]));
});

it('has many clients', function () {
    $data = ParentClient::factory()
        ->hasClients()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->clients()
    );

    $this->assertInstanceOf(
        Client::class,
        $data->clients->first()
    );
});

// it('has many invoices', function () {
//     $data = ParentClient::factory()
//         ->hasInvoices()
//         ->create();

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\HasMany::class,
//         $data->invoices()
//     );

//     $this->assertInstanceOf(
//         \App\Models\Invoice::class,
//         $data->invoices()->first()
//     );
// });

it('gather invoice prefix name', function() {
    $data = ParentClient::factory()->create(['name' => 'Some random name']);

    expect($data->invoice_prefix)
        ->toBe('SOMERN');
});
