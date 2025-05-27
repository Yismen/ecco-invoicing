<?php

use App\Models\Item;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Campaign;
use App\Models\InvoiceItem;
use App\Enums\InvoiceStatuses;
use App\Models\Project;

it('save correct fields', function () {
    $data = Invoice::factory()->make();

    Invoice::create($data->toArray());

    $this->assertDatabaseHas(Invoice::class, $data->only([
        // 'number',
        'date',
        'project_id',
        'agent_id',
        'campaign_id',
    //     'data',
    //     'subtotal_amount',
    //     'tax_amount',
    //     'total_amount',
    //     'status',
    //     'due_date',
    ]));
});

it('belongs to a project', function () {
    $data = Invoice::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->project()
    );

    $this->assertInstanceOf(
        \App\Models\Project::class,
        $data->agent->project
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

it('belongs to a campaign', function () {
    $data = Invoice::factory()
        ->forCampaign()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->campaign()
    );
    $this->assertInstanceOf(
        \App\Models\Campaign::class,
        $data->campaign
    );
});

it('belongs to many items', function () {
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

it('has many invoice items', function () {
    $data = Invoice::factory()
        ->hasItems()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->invoiceItems()
    );

    $this->assertInstanceOf(
        \App\Models\InvoiceItem::class,
        $data->invoiceItems->first()
    );
});

it('updates the due date based on project net terms', function () {
    $project = Project::factory()
        ->create(['invoice_net_days' => 30]);

    $data = Invoice::factory()
        ->create([
            'project_id' => $project->id,
            'date' => now(),
            'due_date' => null,
        ]);

    $this->assertEquals($data->due_date, today()->addDays(30));
});


it('calculates subtotal, taxt amount and total amount', function () {
    $project = Project::factory()
        ->create(['tax_rate' => 0.2]);
    $items = Item::factory()
        ->count(3)
        ->create();
    $data = Invoice::factory()
        ->create(['project_id' => $project->id]);

   foreach ($items as $item) {
        $data->invoiceItems()->create([
            'item_id' => $item->id,
            'item_price' => 10,
            'quantity' => 4,
    ]);
   }

   $data->touch();

    $subtotal = 3 * 10 * 4;
    $tax = $subtotal * $project->tax_rate;
    $total = $subtotal + $tax;

    $this->assertEquals($data->subtotal_amount, $subtotal);
    $this->assertEquals($data->tax_amount, $tax);
    $this->assertEquals($data->total_amount, $total);
});

it('updates the number based on the project', function() {
    config()->set('app.company.short_name', 'ECC');
    config()->set('app.company.invoice_length', 8);
    $data = Invoice::factory()
        ->for(
            Project::factory()
                ->state(['name' => 'Project Name one'])
                ->for(
                    Client::factory()
                        ->state(['name' => 'cLIENT number one'])
                )
        )
        ->create();

    expect($data->number)
        ->toBe('ECC-CLIENT-PROJECT-00000001');
});

it('has many payments', function () {
    $data = Invoice::factory()
        ->has(Payment::factory(state: ['amount' => 0]))
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class,
        $data->payments()
    );
    $this->assertInstanceOf(
        Payment::class,
        $data->payments->first()
    );
});

it('allows partial payments and calculates total paid', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);

    expect($invoice->total_paid)->toBe(200.00);
});

it('allows partial payments and calculates remaining balance', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);

    $invoice->touch();

    $totalPaid = $invoice->fresh()->total_paid;

    expect($totalPaid)->toBe(200.00);

    expect($invoice->balance_pending)->toBe(100.00);
});

it('prevents a payment that exceeds invoice total', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 200.00,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 200.00,
    ]);
})->throws(\Exception::class);

it('has status pending by default', function() {
    $data = Invoice::factory()->create();

    expect($data->status)
        ->toBe(InvoiceStatuses::Pending);
});

it('has status overdue when it hasnt been paid and due day has passed', function() {
    $data = Invoice::factory()
        ->for(Project::factory()->state(['invoice_net_days' => 10]))
        ->create(['status' => InvoiceStatuses::Pending]);

    $this->travel(15)->days();

    $data->touch();

    expect($data->status)
        ->toBe(InvoiceStatuses::Overdue);
});

it('has status partially paid when payment is created but invoice still have some balance', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 200,
    ]);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatuses::PartiallyPaid);
});


it('has status paid when payment is created and balance is 0', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 300,
    ]);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatuses::Paid);
});
