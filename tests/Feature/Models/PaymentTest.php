<?php

use App\Exceptions\InvoiceOverpaymentException;
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
    $data = [
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
        'date' => now(),
        'reference' => 'REF123',
        'description' => 'Payment for invoice',
    ];

    Payment::factory()->create($data);

    $data['amount'] = $data['amount'] * 100; // Convert to cents

    $this->assertDatabaseHas(Payment::class, $data);
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

it('saves the price as a integer', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
    ]);

    $this->assertDatabaseHas(Payment::class, [
        'id' => $payment->id,
        'amount' => 20000, // Stored as integer in cents
    ]);
});

it('retrieves the price as a float', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
    ]);

    $this->assertEquals(200.00, $payment->refresh()->amount);
});

it('prevents overpayments', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 3000.00,
    ]);
})->throws(InvoiceOverpaymentException::class);

