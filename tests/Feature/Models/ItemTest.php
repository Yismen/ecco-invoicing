<?php

use App\Models\Item;

it('save correct fields', function () {
    $data = Item::factory()->make();

    Item::create($data->toArray());

    $this->assertDatabaseHas(Item::class, $data->only([
        'name',
        'campaign_id',
        'price',
        'description',
        'image',
        'category',
        'brand',
        'sku',
        'barcode',
    ]));
});

it('belongs to a campaign', function () {
    $data = Item::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->campaign()
    );
    $this->assertInstanceOf(
        \App\Models\Campaign::class,
        $data->campaign
    );
});

it('has many invoices', function () {
    $data = Item::factory()
        ->hasInvoices()
        ->create();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
        $data->invoices()
    );
    $this->assertInstanceOf(
        \App\Models\Invoice::class,
        $data->invoices->first()
    );
});
