<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Payment;
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
});

it('passes when the new payment less than invoice total', function () {
    expect(Validator::make(
        ['amount' => 500],
        ['amount' => new PreventOverpayment($this->invoice->fresh())]
    )->passes())
        ->toBeTrue();
});

it('passes when the new payment is equal or less to the invoice total', function () {
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
    ]);

    expect(Validator::make(
        ['amount' => 300],
        ['amount' => new PreventOverpayment($this->invoice->fresh())]
    )->passes())
        ->toBeTrue();
});

it('passes when editing a payment lower or equal invoice total', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200,
    ]);

    expect(Validator::make(
        ['amount' => 500],
        ['amount' => new PreventOverpayment($payment)]
    )->passes())
        ->toBeTrue();
});

it('fails when editing a payment higher than invoice total', function () {
    $payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 500,
    ]);

    expect(Validator::make(
        ['amount' => 700],
        ['amount' => new PreventOverpayment($payment)]
    )->fails())
        ->toBeTrue();
});

it('fails when the new payment would exceed the invoice total', function () {
    Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'amount' => 200.00,
    ]);
    expect(Validator::make(
        ['amount' => 400.00],
        ['amount' => new PreventOverpayment($this->invoice->fresh())]
    )->passes())
        ->toBeFalse();
});
