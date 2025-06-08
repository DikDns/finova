<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthlyBudget>
 */
class MonthlyBudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month' => fake()->date(),
            'total_income' => fake()->randomFloat(2, 0, 1000),
            'total_assigned' => fake()->randomFloat(2, 0, 1000),
            'total_activity' => fake()->randomFloat(2, 0, 1000),
            'total_available' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
