<?php

use App\Enums\InvoiceStatuses;
use App\Exceptions\PreventCancellingInvoiceWithPaymentException;
use App\Models\InvoiceCancellation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;

it('save correct fields', function () {
    $data = InvoiceCancellation::factory()->make();

    InvoiceCancellation::create($data->toArray());

    $this->assertDatabaseHas(InvoiceCancellation::class, $data->only([
        'invoice_id',
        'date',
        'comments',
    ]));
});

it('belogns to an invoice', function () {
    $data = InvoiceCancellation::factory()
        ->hasInvoice()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->invoice()
    );

    $this->assertInstanceOf(
        Invoice::class,
        $data->invoice
    );
});

it('change invoice status when created', function() {
    $invoice = Invoice::factory()
        ->create();

    expect($invoice->status)
        ->toBe(InvoiceStatuses::Pending);

    InvoiceCancellation::factory()->create([
        'invoice_id' => $invoice->id
    ]);

    expect($invoice->fresh()->status)
        ->toBe(InvoiceStatuses::Cancelled);

});

it('prevents cancelling invoices with payments', function() {
    $invoice = Invoice::factory()
        ->has(InvoiceItem::factory())
        ->create();

        $invoice->touch();

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 5
    ]);

    InvoiceCancellation::factory()->create([
        'invoice_id' => $invoice->id
    ]);

})->throws(PreventCancellingInvoiceWithPaymentException::class, 'Invoice has payments already!');
