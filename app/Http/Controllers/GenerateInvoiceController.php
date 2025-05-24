<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice as PrintInvoice;
use LaravelDaily\Invoices\Classes\InvoiceItem as PrintItem;

class GenerateInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $client = new Party([
            'name'          => 'Roosevelt Lloyd',
            'phone'         => '(520) 318-9486',
            'custom_fields' => [
                'note'        => 'IDDQD',
                'business id' => '365#GG',
            ],
        ]);

        $customer = new Party([
            'name'          => 'Ashley Medina',
            'address'       => 'The Green Street 12',
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

        $pdf = PrintInvoice::make('Invoice')
            ->series($invoice->number)
            // ability to include translated invoice status
            // in case it was paid
            ->status(__('invoices::invoice.paid'))
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
            ->template($invoice->project->client->invoice_template)
            ->notes($notes)
            // ->logo(public_path('vendor/invoices/ecco-logo.png'))
            // You can additionally save generated invoice to configured disk
            ->save('public');
        // Then send email to party with link

        // And return invoice itself to browser or have a different view
        return $pdf->stream();
        // return $invoice->download();


    }
}
