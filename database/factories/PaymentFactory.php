<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => \App\Models\Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'date' => $this->faker->date(),
            'method' => $this->faker->randomElement(['credit_card', 'bank_transfer', 'cash']),
            'reference' => $this->faker->word(),
            'notes' => $this->faker->text(),
            'images' => $this->faker->imageUrl(),
            'description' => $this->faker->text(),
            // 'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
        ];
    }
}
