<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryBudget>
 */
class CategoryBudgetFactory extends Factory
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
            'assigned' => fake()->randomFloat(2, 0, 1000),
            'activity' => fake()->randomFloat(2, 0, 1000),
            'available' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
