<?php

use App\Models\Item;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoiceItem;
use App\Rules\PreventOverpayment;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    $rule = new PreventOverpayment($this->invoice->id);

    // Current paid = 200, invoice total = 500, so 300 is OK
    expect($rule->passes('amount', 300.00))->toBeTrue();
});

it('fails when the new payment would exceed the invoice total', function () {
    $rule = new PreventOverpayment($this->invoice->id);

    // Current paid = 200, invoice total = 500, so 400 would exceed
    expect($rule->passes('amount', 400.00))->toBeFalse();
    expect($rule->message())->toBe('The payment would exceed the invoice total.');
});
