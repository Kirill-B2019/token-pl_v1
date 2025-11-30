<?php

namespace Database\Factories;

use App\Models\Token;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'transaction_id' => 'txn_' . Str::random(16),
            'user_id' => User::factory(),
            'token_id' => Token::factory(),
            'type' => $this->faker->randomElement(['buy', 'sell', 'transfer', 'refund', 'deposit']),
            'deposit_type' => $this->faker->randomElement(['token', 'rub']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'cancelled']),
            'amount' => $this->faker->randomFloat(8, 0.0001, 1000),
            'price' => $this->faker->randomFloat(8, 0.01, 100),
            'total_amount' => $this->faker->randomFloat(8, 0.01, 10000),
            'fee' => $this->faker->randomFloat(8, 0, 10),
            'payment_method' => $this->faker->randomElement(['card', 'bank_transfer', 'crypto']),
            'payment_reference' => 'ref_' . Str::random(12),
            'metadata' => null,
            'processed_at' => $this->faker->optional()->dateTime(),
        ];
    }

    /**
     * Indicate that the transaction is for token deposit.
     */
    public function tokenDeposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'deposit',
            'deposit_type' => 'token',
            'token_id' => Token::factory(),
        ]);
    }

    /**
     * Indicate that the transaction is for RUB deposit.
     */
    public function rubDeposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'deposit',
            'deposit_type' => 'rub',
            'token_id' => null,
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Indicate that the transaction is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}