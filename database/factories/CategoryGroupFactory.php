<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryGroup>
 */
class CategoryGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Category Group random name
            'name' => fake()->randomElement(['Pendidikan', 'Makanan', 'Transportasi', 'Belanja', 'Hiburan'])
        ];
    }
}
