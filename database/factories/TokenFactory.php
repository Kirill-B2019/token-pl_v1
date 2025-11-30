<?php

namespace Database\Factories;

use App\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Token>
 */
class TokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => strtoupper($this->faker->unique()->word()),
            'name' => $this->faker->words(2, true),
            'current_price' => $this->faker->randomFloat(8, 0.000001, 1000),
            'total_supply' => $this->faker->randomFloat(0, 1000000, 100000000),
            'available_supply' => $this->faker->randomFloat(0, 100000, 50000000),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the token is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the token is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}