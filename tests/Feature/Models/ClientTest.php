<?php

use App\Models\Agent;
use App\Models\Client;
use App\Models\Project;

it('save correct fields', function () {
    $data = Client::factory()->make();

    Client::create($data->toArray());

    $this->assertDatabaseHas(Client::class, $data->only([
        'name',
        'invoice_template',
        'template_date_field_name',
        'template_project_field_name',
    ]));
});

it('has many projects', function () {
    $data = Client::factory()
        ->hasProjects()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->projects()
    );

    $this->assertInstanceOf(
        Project::class,
        $data->projects->first()
    );
});

// it('has many invoices thru projects', function () {
//     $data = Client::factory()
//         ->has(
//             Project::factory()
//                 ->hasInvoices()
//         )
//         ->create();

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\HasManyThrough::class,
//         $data->invoices()
//     );

//     $this->assertInstanceOf(
//         \App\Models\Invoice::class,
//         $data->invoices()->first()
//     );
// });

it('gather invoice prefix name', function() {
    $data = Client::factory()->create(['name' => 'Some random name']);

    expect($data->invoiceNamePrefix())
        ->toBe('SOME');
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

// it('has many campaigns', function () {
//     $data = Client::factory()
//         ->hasCampaigns()
//         ->create();

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\HasMany::class,
//         $data->campaigns()
//     );
//     $this->assertEquals(
//         $data->getForeignKeyName(),
//         $data->campaigns()->getForeignKeyName()
//     );
// });
