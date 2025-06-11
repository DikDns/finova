<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payee' => fake()->company(),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'amount' => fake()->randomFloat(4, -1000, 1000),
            'memo' => fake()->sentence(),
        ];
    }
}
