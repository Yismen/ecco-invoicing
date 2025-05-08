<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'project_id' => \App\Models\Project::factory(),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->sentence(),
            'image' => $this->faker->imageUrl(),
            'category' => $this->faker->word(),
            'brand' => $this->faker->word(),
            'sku' => $this->faker->word(),
            'barcode' => $this->faker->ean13(),
        ];
    }
}
