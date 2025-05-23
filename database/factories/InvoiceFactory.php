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
            'date' => $this->faker->date(),
            'project_id' => \App\Models\Project::factory(),
            'agent_id' => \App\Models\Agent::factory(),
            'campaign_id' => \App\Models\Campaign::factory(),
            'data' => $this->faker->text(),
            'subtotal_amount' => $this->faker->randomFloat(2, 0, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 0, 1000),
            'total_amount' => $this->faker->randomFloat(2, 0, 1000),
            'status' =>InvoiceStatuses::Pending,
            'due_date' => $this->faker->date(),
        ];
    }
}
