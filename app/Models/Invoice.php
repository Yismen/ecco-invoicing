<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\InvoiceStatuses;
use App\Services\GenerateInvoiceNumberService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use \App\Traits\Models\InteracstsWithModelCaching;

    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'number',
        'date',
        'project_id',
        'agent_id',
        'campaign_id',
        'data',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'total_paid',
        'balance_pending',
        'status',
        'due_date',
    ];

    protected $casts = [
        'status' => InvoiceStatuses::class,
        'due_date' => 'date',
        'date' => 'date',
        'data' => 'array',
        'total_amount' => AsMoney::class,
        'subtotal_amount' => AsMoney::class,
        'tax_amount' => AsMoney::class,
        'total_paid' => AsMoney::class,
        'balance_pending' => AsMoney::class,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $invoice) {
            $invoice->number = null;
            $invoice->number = GenerateInvoiceNumberService::generate($invoice->project);
        });

        static::saved(function (self $invoice) {
            $invoice->load(['invoiceItems.item', 'project.client', 'agent', 'campaign', 'payments']);

            $subtotal_amount = 0;

            foreach ($invoice->invoiceItems as $item) {
                $subtotal_amount += $item->item_price * $item->quantity;
            }

            $tax_amount = round($subtotal_amount * ($invoice->project->tax_rate ?: 0), 10);
            $total_amount = round($subtotal_amount + $tax_amount, 10);
            $total_paid = round($invoice->payments->sum('amount'), 10);

            $invoice->updateQuietly([
                'subtotal_amount' => $subtotal_amount,
                'tax_amount' => $tax_amount,
                'total_amount' => $total_amount,
                'total_paid' => $total_paid,
                'balance_pending' => $total_amount - $total_paid,
                'due_date' => $invoice->date->addDays($invoice->project->invoice_net_days ?: 0),
            ]);

            $invoice->updateQuietly([
                'status' => $invoice->getStatus(),
                'data' => [
                    'client' => $invoice->project->client->only(['id', 'name', 'invoice_template', 'template_date_field_name', 'template_project_field_name']),
                    'agent' => $invoice->agent ? $invoice->agent->only(['id', 'name']) : null,
                    'campaign' => $invoice->campaign ? $invoice->campaign->only(['id', 'name']) : null,
                    'project' => $invoice->project->only(['id', 'name']),
                    'invoiceItems' => $invoice->invoiceItems->map(fn ($item) => [
                        'name' => $item->item->name,
                        'price' => $item->item_price,
                        'quantity' => $item->quantity,
                        'total' => $item->item_price * $item->quantity,
                    ])->toArray(),
                    'payments' => $invoice->payments->map(fn ($payment) => [
                        'amount' => $payment->amount,
                        'date' => $payment->date,
                        'reference' => $payment->reference,
                        'description' => $payment->description,
                        'images' => $payment->images,
                    ])->toArray(),
                ],
            ]);
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(InvoiceCancellation::class, 'invoice_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function getStatus(): InvoiceStatuses
    {
        if ($this->total_paid == 0 && $this->cancellation?->exists()) {
            return InvoiceStatuses::Cancelled;
        }

        if ($this->total_paid > 0) {
            return $this->balance_pending > 0 ? InvoiceStatuses::PartiallyPaid : InvoiceStatuses::Paid;
        }

        if ($this->due_date->isPast()) {
            return InvoiceStatuses::Overdue;
        }

        return InvoiceStatuses::Pending;
    }

    protected function number(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?: GenerateInvoiceNumberService::generate($this->project),
        );
    }
}
