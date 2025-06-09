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
            'invoice' => 'INV-' . fake()->unique()->numerify('######'),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'credit_card', 'debit_card', 'e_wallet']),
            'start_date' => $startDate = fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween($startDate, '+1 year'),
        ];
    }
}
