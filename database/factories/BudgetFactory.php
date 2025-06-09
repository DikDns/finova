<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 0, 10000),
            'currency_code' => fake()->randomElement(['IDR', 'USD', 'EUR', 'GBP', 'JPY']),
        ];
    }
}
