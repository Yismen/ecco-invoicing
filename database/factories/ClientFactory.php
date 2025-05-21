<?php

namespace Database\Factories;

use App\Models\ParentClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'parent_client_id' => ParentClient::factory(),
            'address' => $this->faker->address,
            'tax_rate' => 0,
            'invoice_template' => $this->faker->randomElement(['template1', 'template2']),
            'invoice_notes' => $this->faker->text(100),
            'invoice_terms' => $this->faker->text(100),
            'invoice_net_days' => $this->faker->numberBetween(1, 30),
        ];
    }
}
