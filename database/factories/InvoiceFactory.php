<?php

namespace Database\Factories;

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
            'agent_id' => \App\Models\Agent::factory(),
            'data' => $this->faker->text(),
            'subtotal_amount' => $this->faker->randomFloat(2, 0, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 0, 1000),
            'total_amount' => $this->faker->randomFloat(2, 0, 1000),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid']),
            'due_date' => $this->faker->date(),
        ];
    }
}
