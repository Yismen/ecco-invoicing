<?php

use App\Models\Agent;
use App\Models\Invoice;

it('save correct fields', function () {
    $data = Invoice::factory()->make();

    Invoice::create($data->toArray());

    $this->assertDatabaseHas(Invoice::class, $data->only([
        'number',
        'date',
        'agent_id',
        'data',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'status',
        'due_date',
        'template',
        'notes',
        'terms',
    ]));
});

it('belongs to an agent', function () {
    $data = Invoice::factory()
        ->forAgent()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->agent()
    );
    $this->assertInstanceOf(
        \App\Models\Agent::class,
        $data->agent
    );
});

it('belongs to a project', function () {
    $agent = Agent::factory()
        ->hasProjects()
        ->create();
    $data = Invoice::factory()
        ->create(['agent_id' => $agent->id]);

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->project()
    );
    $this->assertInstanceOf(
        \App\Models\Project::class,
        $data->agent->projects->first()
    );
});

// it('belongs to a client', function () {
//     $data = Invoice::factory()->make();

//     dd($data->agent->toArray());

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
//         $data->client()
//     );
//     $this->assertInstanceOf(
//         \App\Models\Client::class,
//         $data->agent->client
//     );
// });

// it('has many payments', function () {
//     $data = Invoice::factory()->make();

//     $this->assertInstanceOf(
//         \Illuminate\Database\Eloquent\Relations\HasMany::class,
//         $data->payments()
//     );
//     $this->assertEquals(
//         \App\Models\Payment::class,
//         $data->payments()->first()
//     );
// });

it('has many invoice items', function () {
    $data = Invoice::factory()
        ->hasItems()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
        $data->items()
    );

    $this->assertInstanceOf(
        \App\Models\Item::class,
        $data->items->first()
    );
});

// it('change status when paid', function () {
//     $data = Invoice::factory()->make();

//     $this->assertEquals('unpaid', $data->status);

//     $data->markAsPaid();

//     $this->assertEquals('paid', $data->status);
// });

// it('change status when unpaid', function () {
//     $data = Invoice::factory()->make();

//     $this->assertEquals('unpaid', $data->status);

//     $data->markAsPaid();
//     $this->assertEquals('paid', $data->status);

//     $data->markAsUnpaid();
//     $this->assertEquals('unpaid', $data->status);
// });

// it('change status when partially paid', function () {
//     $data = Invoice::factory()->make();

//     $this->assertEquals('unpaid', $data->status);

//     $data->markAsPaid();
//     $this->assertEquals('paid', $data->status);

//     $data->markAsPartiallyPaid();
//     $this->assertEquals('partially_paid', $data->status);
// });

// it('change status when cancelled', function () {
//     $data = Invoice::factory()->make();

//     $this->assertEquals('unpaid', $data->status);

//     $data->markAsCancelled();
//     $this->assertEquals('cancelled', $data->status);
// });

// it('change status when draft', function () {
//     $data = Invoice::factory()->make();

//     $this->assertEquals('unpaid', $data->status);

//     $data->markAsDraft();
//     $this->assertEquals('draft', $data->status);
// });

// it('change status when sent', function () {
//     $data = Invoice::factory()->make();

//     $this->assertEquals('unpaid', $data->status);

//     $data->markAsSent();
//     $this->assertEquals('sent', $data->status);
// });
