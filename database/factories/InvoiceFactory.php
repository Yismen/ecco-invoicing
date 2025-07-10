<?php

namespace Database\Factories;

use App\Enums\InvoiceStatuses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numerify('INV-#####'),
            'date' => now(),
            'project_id' => \App\Models\Project::factory(),
            'agent_id' => \App\Models\Agent::factory(),
            'campaign_id' => \App\Models\Campaign::factory(),
            // 'data' => $this->faker->text(),
            'subtotal_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'total_paid' => 0,
            'balance_pending' => 0,
            'status' => InvoiceStatuses::Pending,
            // 'due_date' => $this->faker->date(),
        ];
    }
}
