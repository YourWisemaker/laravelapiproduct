<?php

namespace Database\Factories;

use App\Models\RentalPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RentalPeriod>
 */
class RentalPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Daily', 'Weekly', 'Monthly', 'Yearly']),
            'days' => fake()->randomElement([1, 7, 30, 365]),
            'is_active' => true,
        ];
    }
}