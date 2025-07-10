<?php

use App\Enums\InvoiceStatuses;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use Illuminate\Console\Scheduling\Event;

it('run successfully', function () {
    $this->artisan('app:update-invoices-status')
        ->expectsOutput('Invoices status updated successfully.')
        ->assertExitCode(0);
});

it('updates invoices status', function () {
    $invoice = Invoice::factory()
        ->create();
    $item = Item::factory()->create(['price' => 300]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'item_price' => $item->price,
    ]);

    assert($invoice->status === InvoiceStatuses::Pending);

    $this->travelTo(now()->addDays(180));

    $this->artisan('app:update-invoices-status');

    expect(Invoice::find($invoice->id)->status)
        ->toBe(InvoiceStatuses::Overdue);

    $this->travelBack();
});

it('is schedule to run daily at 2:00 AM', function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $command = 'app:update-invoices-status';

    $events = collect($schedule->events())->filter(function (Event $event) use ($command) {
        return stripos($event->command, $command) !== false;
    });

    expect($events)
        ->toHaveCount(1)
        ->and($events->first()->expression)
        ->toBe('0 3 * * *');
});
