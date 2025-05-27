<?php

use App\Models\Item;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoiceItem;
use App\Rules\PreventOverpayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

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

    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
    ]);
});

it('passes when the new payment does not exceed the invoice total', function () {
    // dd($this->invoice->fresh()->toArray());
    // Current paid = 200, invoice total = 500, so 300 is OK
    expect(Validator::make(
        ['amount' => 10.00],
        ['amount' => new PreventOverpayment($this->invoice->fresh())]
    )->passes())
        ->toBeTrue();
});

it('fails when the new payment would exceed the invoice total', function () {
    expect(Validator::make(
        ['amount' => 400.00],
        ['amount' => new PreventOverpayment($this->invoice->fresh())]
    )->passes())
        ->toBeFalse();
});
