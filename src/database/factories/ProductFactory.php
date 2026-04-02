<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_name' => $this->faker->words(3, true),
            'category_id' => $this->faker->numberBetween(1, 5), // Тестовые категории 1-5
            'manufacturer_id' => \App\Models\Manufacturer::factory(),
        ];
    }
}
