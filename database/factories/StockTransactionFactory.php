<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\User;

class StockTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => fake()->randomElement(['in', 'out', 'adjustment']),
            'quantity' => fake()->numberBetween(1, 50),
            'reference_no' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}