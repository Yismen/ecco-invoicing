<?php

use App\Models\Payment;

it('save correct fields', function () {
    $data = Payment::factory()->make();

    Payment::create($data->toArray());

    $this->assertDatabaseHas(Payment::class, $data->only([
        'invoice_id',
        'amount',
        'date',
        'reference',
        'images',
        'description',
    ]));
});

it('belongs to an invoice', function () {
    $data = Payment::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->invoice()
    );
    $this->assertInstanceOf(
        \App\Models\Invoice::class,
        $data->invoice()->getRelated()
    );
});
