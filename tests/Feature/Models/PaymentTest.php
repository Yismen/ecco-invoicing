<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Payment;

beforeEach(function () {

    $this->invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 500]);

    InvoiceItem::create([
        'invoice_id' => $this->invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);
});

it('save correct fields', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
        'reference' => 'REF123',
        'description' => 'Payment for invoice',
    ]);

    $this->assertDatabaseHas(Payment::class, $payment->only([
        'invoice_id',
        'amount',
        // 'date',
        'reference',
        // 'images',
        'description',
    ]));
});

it('belongs to an invoice', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
    ]);

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $payment->invoice()
    );
    $this->assertInstanceOf(
        \App\Models\Invoice::class,
        $payment->invoice()->getRelated()
    );
});
