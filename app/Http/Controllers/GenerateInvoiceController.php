<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\GenerateInvoiceService;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice as PrintInvoice;
use LaravelDaily\Invoices\Classes\InvoiceItem as PrintItem;

class GenerateInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $invoice->load([
            'project.client',
            'invoiceItems.item',
        ]);

        $client = new Party([
            'name'          => 'Roosevelt Lloyd',
            'phone'         => '(520) 318-9486',
            'custom_fields' => [
                'note'        => 'IDDQD',
                'business id' => '365#GG',
            ],
        ]);

        $customer = new Party([
            'name'          => $invoice->agent->name,
            'company'       => $invoice->project->name,
            'address'       => $invoice->project->address,
            'code'          => '#22663214',
            'custom_fields' => [
                'order number' => '> 654321 <',
            ],
        ]);

        $items = [];
        foreach ($invoice->invoiceItems as $invoiceItem) {
            $items[] = PrintItem::make($invoiceItem->item->name)
                ->pricePerUnit($invoiceItem->item_price)
                ->quantity($invoiceItem->quantity);
        }

        $notes = [
            'your multiline',
            'additional notes',
            'in regards of delivery or something else',
        ];

        $notes = implode("<br>", $notes);

        $pdf = GenerateInvoiceService::make('Invoice')
            ->series($invoice->number)
            ->model($invoice)
            // ->extraData(['model' => $invoice])
            // ability to include translated invoice status
            // in case it was paid
            ->status($invoice->status->value)
            // ->sequence(667)
            // ->serialNumberFormat('{SEQUENCE}/{SERIES}')
            ->seller($client)
            ->buyer($customer)
            ->date($invoice->date)
            // ->dateFormat('m/d/Y')
            ->payUntilDays(14)
            ->currencySymbol('$')
            ->currencyCode('USD')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyThousandsSeparator(',')
            ->currencyDecimalPoint('.')
            ->filename($invoice->number)
            ->addItems($items)
            ->template('publishing')
            ->notes($notes)
            ->logo(public_path('img/ecco-logo.png'))
            // You can additionally save generated invoice to configured disk
            ->save('public');
        // Then send email to party with link

        // And return invoice itself to browser or have a different view
        return $pdf->stream();
        // return $invoice->download();


    }
}
