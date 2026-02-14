<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use LaravelDaily\Invoices\Classes\InvoiceItem as PrintItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Facades\Invoice as LaravelDailyInvoice;

class GenerateInvoiceService
{
    public $pdf;

    protected $disk;

    public Invoice $invoice;

    public function generate(Invoice $invoice, string $disk = 'public'): self
    {
        $this->disk = $disk;
        $this->invoice = $invoice;
        $this->invoice->load(['invoiceItems.item', 'agent', 'project.client', 'payments']);

        $customer = new Party([
            'name' => $this->invoice->agent->name,
            'company' => $this->invoice->project->name,
            'address' => $this->invoice->project->address,
        ]);

        $items = [];
        foreach ($this->invoice->invoiceItems as $invoiceItem) {
            $items[] = PrintItem::make($invoiceItem->item->name)
                ->pricePerUnit($invoiceItem->item_price)
                ->quantity($invoiceItem->quantity);
        }

        $this->pdf = LaravelDailyInvoice::make('Invoice')
            ->series($this->invoice->number)
            ->setCustomData(['model' => $this->invoice])
            ->status($this->invoice->status->value)
            ->buyer($customer)
            ->date($this->invoice->date)
            ->currencySymbol('$')
            ->currencyCode('USD')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyThousandsSeparator(',')
            ->currencyDecimalPoint('.')
            ->filename($this->invoice->number)
            ->addItems($items)
            ->template($this->invoice->project->client->invoice_template)
            ->logo(public_path(config('app.company.logo')))
            ->save($this->disk);

        $this->unlinkFile();

        return $this;
    }

    public function toHtml()
    {
        return $this->pdf->toHtml();

    }

    public function toStream()
    {
        return $this->pdf->stream();
    }

    public function toFile()
    {
        return $this->pdf->download();
    }

    protected function unlinkFile()
    {
        if (Storage::disk($this->disk)->exists($this->pdf->filename)) {
            Storage::disk($this->disk)->delete($this->pdf->filename);
        }
    }
}
