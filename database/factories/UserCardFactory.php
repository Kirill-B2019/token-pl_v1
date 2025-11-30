<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserCard>
 */
class UserCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expiryYear = $this->faker->numberBetween(2025, 2030);
        $expiryMonth = $this->faker->numberBetween(1, 12);

        return [
            'user_id' => User::factory(),
            'twocan_card_token' => 'card_token_' . $this->faker->unique()->uuid(),
            'card_mask' => $this->faker->regexify('[0-9]{6}\*\*\*\*\*\*[0-9]{4}'),
            'card_brand' => $this->faker->randomElement(['Visa', 'MasterCard', 'American Express']),
            'card_holder_name' => strtoupper($this->faker->name()),
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'is_active' => true,
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the card is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the card is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the card is default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the card is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_year' => 2020,
            'expiry_month' => 1,
        ]);
    }
}