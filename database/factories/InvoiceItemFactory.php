<?php

namespace Database\Factories;

use App\Enums\InvoiceStatuses;
use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'invoice_id' => Invoice::factory(),
            'quantity' => 1,
            'item_price' => 10,
        ];
    }
}
