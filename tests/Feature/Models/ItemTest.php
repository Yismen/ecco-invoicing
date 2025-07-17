<?php

use App\Models\Item;

it('save correct fields', function () {
    $data = Item::factory()->make();

    Item::create($data->toArray());

    $data->price = $data->price * 100; // Convert to cents

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

it('belogns to many invoices', function () {
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

it('saves the price as a integer', function () {
    $data = Item::factory()->make([
        'price' => 100.50,
    ]);

    $item = Item::create($data->toArray());

    $this->assertDatabaseHas(Item::class, [
        'id' => $item->id,
        'price' => 10050, // Stored as integer in cents
    ]);
});

it('retrieves the price as a float', function () {
    $data = Item::factory()->create([
        'price' => 100.50, // Stored as integer in cents
    ]);

    $this->assertEquals(100.50, $data->price);

    $this->assertDatabaseHas(Item::class, [
        'id' => $data->id,
        'price' => 10050, // Stored as integer in cents
    ]);
});
