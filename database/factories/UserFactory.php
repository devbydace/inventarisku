<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['admin', 'kasir', 'audit', 'manager', 'staff_toko']),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Configure the factory to assign Spatie role after creating.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->assignRole($user->role);
        });
    }
}