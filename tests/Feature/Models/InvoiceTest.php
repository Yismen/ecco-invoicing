<?php

use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Project;

it('save correct fields', function () {
    $data = Invoice::factory()->make();

    Invoice::create($data->toArray());

    $this->assertDatabaseHas(Invoice::class, $data->only([
        'number',
        'date',
        'agent_id',
        'data',
        // 'subtotal_amount',
        // 'tax_amount',
        // 'total_amount',
        // 'status',
        // 'due_date',
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

it('updates the due date based on client net terms', function () {
    $client = Client::factory()
        ->create(['invoice_net_days' => 30]);
    $data = Invoice::factory()
        ->for(
            Agent::factory()
                ->state([
                    'client_id' => $client->id,
                ])
        )
        ->create([
            'date' => now(),
            'due_date' => null,
        ]);

    // Assuming the client has net terms set
    $this->assertEquals($data->due_date, now()->addDays(30)->format('Y-m-d'));
});


it('calculates subtotal, taxt amount and total amount', function () {
    $client = Client::factory()
        ->create(['tax_rate' => 0.2]);
    $items = Item::factory()
        ->count(3)
        ->create();
    $data = Invoice::factory()
        ->for(
            Agent::factory()
                ->state([
                    'client_id' => $client->id,
                ])
        )
        ->create();

    $data->items()->sync($items->pluck('id'));
    $data->update([]);

    $subtotal = $data->items->sum('price') * $data->items->count();
    $tax = $subtotal * $client->tax_rate;
    $total = $subtotal + $tax;

    $this->assertEquals($data->subtotal_amount, $subtotal);
    $this->assertEquals($data->tax_amount, $tax);
    $this->assertEquals($data->total_amount, $total);
});
//calculates tax based on subtotal and client tax rate
// it('calculates tax based on subtotal and client tax rate', function () {
//     $client = Client::factory()
//         ->create(['tax_rate' => 0.2]);
//     $data = Invoice::factory()
//         ->for(
//             Agent::factory()
//                 ->state([
//                     'client_id' => $client->id,
//                 ])
//         )
//         ->hasItems(3)
//         ->create();

//     $subtotal = $data->items->sum('price');
//     $tax = $subtotal * $client->tax_rate;

//     $this->assertEquals($data->tax_amount, $tax);
// });
//calculates total based on subtotal and tax
// it('calculates total based on subtotal and tax', function () {
//     $client = Client::factory()
//         ->create(['tax_rate' => 0.2]);
//     $data = Invoice::factory()
//         ->for(
//             Agent::factory()
//                 ->state([
//                     'client_id' => $client->id,
//                 ])
//         )
//         ->hasItems(3)
//         ->create();

//     $subtotal = $data->items->sum('price');
//     $tax = $subtotal * $client->tax_rate;
//     $total = $subtotal + $tax;

//     $this->assertEquals($data->total_amount, $total);
// });
