<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiChat>
 */
class AiChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
            'category_ids' => json_encode([fake()->numberBetween(1, 10)]),
            'transaction_ids' => json_encode([fake()->numberBetween(1, 10)]),
            'account_ids' => json_encode([fake()->numberBetween(1, 10)]),
        ];
    }
}
