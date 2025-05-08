<?php

use App\Models\Item;

it('save correct fields', function () {
    $data = Item::factory()->make();

    Item::create($data->toArray());

    $this->assertDatabaseHas(Item::class, $data->only([
        'name',
        'project_id',
        'price',
        'description',
        'image',
        'category',
        'brand',
        'sku',
        'barcode',
    ]));
});

it('belongs to a project', function () {
    $data = Item::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->project()
    );
    $this->assertInstanceOf(
        \App\Models\Project::class,
        $data->project
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
