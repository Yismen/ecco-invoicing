<?php

namespace App\Models;

use App\Enums\InvoiceStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'status',
        'due_date',
    ];

    protected $casts = [
        'status' => InvoiceStatuses::class,
        'due_date' => 'date',
        'date' => 'date',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $invoice) {
            $invoice->number = join('-', [
                config('app.company.short_name'),
                $invoice->project->client->invoice_prefix,
                $invoice->project->invoice_prefix,
                str($invoice->project->invoices->count() + 1)->padLeft(config('app.company.invoice_length', 8), 0)
            ]);
            $invoice->due_date = now()->addDays($invoice->project->invoice_net_days ?: 0);
            $invoice->status = InvoiceStatuses::Pending;
        });

        static::saved(function (self $invoice) {
            $subtotal_amount = 0;

            foreach ($invoice->invoiceItems as $item) {
                $subtotal_amount += $item->item_price * $item->quantity;
            }

            $tax_amount = $subtotal_amount * ($invoice->project->tax_rate ?: 0);
            $total_amount = $subtotal_amount + $tax_amount;
            $status = $invoice->due_date->isPast() ? InvoiceStatuses::Overdue : InvoiceStatuses::Pending;

            $invoice->updateQuietly([
                'subtotal_amount' => $subtotal_amount,
                'tax_amount' => $tax_amount,
                'total_amount' => $total_amount,
                'status' => $status,
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

    public function getTotalPaidAttribute()
    {
        return (float)$this->payments()->sum('amount');
    }

    public function getBalancePendingAttribute()
    {
        return (float)($this->total_amount - $this->total_paid);
    }
}
