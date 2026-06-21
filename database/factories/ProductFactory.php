<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Unit;

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
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'category_id' => fake()->numberBetween(1, 3),
            'unit_id' => fake()->numberBetween(1, 5),
            'buy_price' => fake()->numberBetween(10000, 500000),
            'sell_price' => fake()->numberBetween(15000, 750000),
            'current_stock' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(5, 20),
            'is_active' => fake()->boolean(90),
        ];
    }
}