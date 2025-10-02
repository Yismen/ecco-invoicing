<?php

use App\Enums\InvoiceStatuses;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Cancellation;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Payment;
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
        // 'data',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'total_paid',
        'balance_pending',
        'status',
        // 'due_date',
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
    $date = now()->subDays(10)->startOfDay();

    $data = Invoice::factory()
        ->create([
            'project_id' => $project->id,
            'date' => $date,
            'due_date' => null,
        ]);

    $this->assertEquals($data->due_date, $date->addDays(30));
});

it('calculates subtotal', function () {
    $items = Item::factory()
        ->count(3)
        ->create();
    $data = Invoice::factory()
        ->create();

    foreach ($items as $item) {
        $data->invoiceItems()->create([
            'item_id' => $item->id,
            'item_price' => 10,
            'quantity' => 4,
        ]);
    }

    $data->touch();

    $subtotal = 3 * 10 * 4;

    $this->assertEquals($data->subtotal_amount, $subtotal);
});

it('calculates taxt amount', function () {
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

    $this->assertEquals($data->tax_amount, $tax);
});

it('calculates total amount', function () {
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

    $this->assertEquals($data->total_amount, $total);
});

it('calculates total amount paid', function () {
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

    Payment::factory()->create(['invoice_id' => $data->id, 'amount' => $totalPaid = 10]);

    $subtotal = 3 * 10 * 4;
    $tax = $subtotal * $project->tax_rate;
    $total = $subtotal + $tax;

    $data->touch();

    $this->assertEquals($data->total_paid, $totalPaid);
});

it('calculates total balance', function () {
    $item = Item::factory()
        ->create([
            'price' => 5.15,
        ]);

    $invoice = Invoice::factory()
        ->create();

    $invoice->invoiceItems()->create([
        'item_id' => $item->id,
        'item_price' => $item->price,
        'quantity' => 360,
    ]);

    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => $invoice->fresh()->total_amount - 100]);

    $this->assertEquals($invoice->fresh()->balance_pending, 100);
});

it('updates the number based on the project', function () {
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
        ->toBe('ECC-CLI-PROJECT-00000001');
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

it('can have a cancellation', function () {
    $data = Invoice::factory()
        ->has(Cancellation::factory(), 'cancellation')
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasOne::class,
        $data->cancellation()
    );

    $data->load('cancellation');

    $this->assertInstanceOf(
        Cancellation::class,
        $data->cancellation
    );
});

it('increase invoice number even if an invoice has a cancelled', function () {
    config()->set('app.company.short_name', 'ECC');
    config()->set('app.company.invoice_length', 8);
    $project = Project::factory()
        ->state(['name' => 'Project Name one'])
        ->for(
            Client::factory()
                ->state(['name' => 'cLIENT number one'])
        )
        ->create();

    $invoice1 = Invoice::factory()
        ->for(
            $project
        )
        ->create();

    $invoice1->cancel('client did not like the work');

    $invoice2 = Invoice::factory()
        ->for($project)
        ->create();

    expect($invoice1->number)
        ->toBe('ECC-CLI-PROJECT-00000001');
    expect($invoice2->number)
        ->toBe('ECC-CLI-PROJECT-00000002');
});

it('increase invoice number even if an invoice has been trashed', function () {
    config()->set('app.company.short_name', 'ECC');
    config()->set('app.company.invoice_length', 8);
    $project = Project::factory()
        ->state(['name' => 'Project Name one'])
        ->for(
            Client::factory()
                ->state(['name' => 'cLIENT number one'])
        )
        ->create();

    $invoice1 = Invoice::factory()
        ->for(
            $project
        )
        ->create();

    $invoice1->delete();

    $invoice2 = Invoice::factory()
        ->for($project)
        ->create();

    expect($invoice1->number)
        ->toBe('ECC-CLI-PROJECT-00000001');
    expect($invoice2->number)
        ->toBe('ECC-CLI-PROJECT-00000002');
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

    expect($invoice->fresh()->total_paid)->toBe(200.0);
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

    expect($totalPaid)->toBe(200.);

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

it('has status pending by default', function () {
    $data = Invoice::factory()->create();

    expect($data->status)
        ->toBe(InvoiceStatuses::Pending);
});

it('has status overdue when it hasnt been paid and due day has passed', function () {
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

    expect($invoice->fresh()->status)->toBe(InvoiceStatuses::PartiallyPaid);
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

    expect($invoice->fresh()->status)->toBe(InvoiceStatuses::Paid);
});

it('saves the price as a integer', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    $this->assertDatabaseHas(Invoice::class, [
        'id' => $invoice->id,
        'subtotal_amount' => 30000, // Stored as integer in cents
        'total_amount' => 30000, // Stored as integer in cents
        'balance_pending' => 30000, // Stored as integer in cents
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 300,
    ]);

    $this->assertDatabaseHas(Invoice::class, [
        'id' => $invoice->id,
        'total_paid' => 30000, // Stored as integer in cents
        'balance_pending' => 0, // Stored as integer in cents
    ]);
});

it('retrieves the price as a float', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    $invoice->refresh();

    $this->assertEquals(300.00, $invoice->subtotal_amount);
    $this->assertEquals(300.00, $invoice->total_amount);
    $this->assertEquals(300.00, $invoice->balance_pending);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 300,
    ]);

    $invoice->refresh();
    $this->assertEquals(300.00, $invoice->total_paid);
    $this->assertEquals(0.00, $invoice->balance_pending);
});
