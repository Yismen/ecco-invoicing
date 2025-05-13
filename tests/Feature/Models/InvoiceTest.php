<?php

use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Project;

it('save correct fields', function () {
    $data = Invoice::factory()->make();

    Invoice::create($data->toArray());

    $this->assertDatabaseHas(Invoice::class, $data->only([
        'number',
        'date',
        'client_id',
        'agent_id',
        'project_id',
        'data',
        // 'subtotal_amount',
        // 'tax_amount',
        // 'total_amount',
        // 'status',
        // 'due_date',
    ]));
});

it('belongs to a client', function () {
    $data = Invoice::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->client()
    );
    $this->assertInstanceOf(
        \App\Models\Client::class,
        $data->agent->client
    );
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
    $data = Invoice::factory()
        ->forProject()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->project()
    );
    $this->assertInstanceOf(
        \App\Models\Project::class,
        $data->project
    );
});

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
        ->create([
            'client_id' => $client->id,
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
        ->create(['client_id' => $client->id]);

   foreach ($items as $item) {
        $data->invoiceItems()->create([
            'item_id' => $item->id,
            'item_price' => 10,
            'quantity' => 4,
    ]);
   }

   $data->touch();

    $subtotal = 3 * 10 * 4; // 3 items at 10 times 4 quantity
    $tax = $subtotal * $client->tax_rate;
    $total = $subtotal + $tax;

    $this->assertEquals($data->subtotal_amount, $subtotal);
    $this->assertEquals($data->tax_amount, $tax);
    $this->assertEquals($data->total_amount, $total);
});
