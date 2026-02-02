<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'date' => $this->date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'status_color' => $this->status->getColor(),

            // Project details
            'project_id' => $this->project_id,
            'project_name' => $this->project?->name,

            // Client details (via project)
            'client_id' => $this->project?->client_id,
            'client_name' => $this->project?->client?->name,

            // Agent details
            'agent_id' => $this->agent_id,
            'agent_name' => $this->agent?->name,

            // Campaign details
            'campaign_id' => $this->campaign_id,
            'campaign_name' => $this->campaign?->name,

            // Financial information
            'subtotal_amount' => $this->subtotal_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'total_paid' => $this->total_paid,
            'balance_pending' => $this->balance_pending,

            // Metadata
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
