<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'client_id',
        'agent_id',
        'project_id',
        'data',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'status',
        'due_date',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $invoice) {
            // $invoice->number = $invoice->client->invoices->count() + 1;
        });

        static::saved(function (self $invoice) {
            $subtotal_amount = 0;

            foreach ($invoice->invoiceItems as $item) {
                $subtotal_amount += $item->item_price * $item->quantity;
            }

            $tax_amount = $subtotal_amount * ($invoice->client->tax_rate ?: 0);
            $due_date = now()->addDays($invoice->client->invoice_net_days ?: 0)->format('Y-m-d');
            $total_amount = $subtotal_amount + $tax_amount;

            $invoice->updateQuietly([
                'subtotal_amount' => $subtotal_amount,
                'tax_amount' => $tax_amount,
                'total_amount' => $total_amount,
                'due_date' => $due_date,
            ]);
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
