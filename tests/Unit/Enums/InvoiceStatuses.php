<?php

use App\Enums\InvoiceStatuses;

it('return the correct names', function () {
    expect(InvoiceStatuses::getNames())
        ->toBe([
            'Pending',
            'Paid',
            'Overdue',
            'Cancelled',
        ]);
});

it('return the correct values', function () {
    expect(InvoiceStatuses::getValues())
        ->toBe([
            'pending',
            'paid',
            'overdue',
            'cancelled',
        ]);
});

it('returns yellow for pending invoices', function () {
    expect(InvoiceStatuses::Pending->getColor())
        ->toBe('yellow');
});

it('returns green for paid invoices', function () {
    expect(InvoiceStatuses::Paid->getColor())
        ->toBe('green');
});

it('returns red for overdue invoices', function () {
    expect(InvoiceStatuses::Overdue->getColor())
        ->toBe('red');
});

it('returns gray for cancelled invoices', function () {
    expect(InvoiceStatuses::Cancelled->getColor())
        ->toBe('gray');
});
