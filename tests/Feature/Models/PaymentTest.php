<?php

use App\Models\Item;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoiceItem;

beforeEach(function() {

        $invoice = Invoice::factory()
            ->create();
        $item = Item::factory()->create(['price' => 500]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'item_price' => $item->price,
        ]);

        $this->payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 200.00,
        ]);
});

it('save correct fields', function () {

    $this->assertDatabaseHas(Payment::class, $this->payment->only([
        'invoice_id',
        'amount',
        // 'date',
        'reference',
        // 'images',
        'description',
    ]));
});

it('belongs to an invoice', function () {
    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $this->payment->invoice()
    );
    $this->assertInstanceOf(
        \App\Models\Invoice::class,
        $this->payment->invoice()->getRelated()
    );
});
