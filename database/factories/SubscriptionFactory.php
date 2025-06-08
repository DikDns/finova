<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // create unique invoice pattern: YEAR-MONTH-DAY-NO
            'invoice' => fake()->unique()->date() . '-' . fake()->unique()->numberBetween(1, 100),
            'payment_method' => fake()->randomElement(['cash', 'bank', 'credit card']),
            'start_date' => fake()->dateTime(),
            'end_date' => fake()->dateTime(),
        ];
    }
}
