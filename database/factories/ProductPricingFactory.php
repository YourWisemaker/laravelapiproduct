<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Region;
use App\Models\RentalPeriod;
use App\Models\ProductPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPricingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductPricing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'region_id' => Region::factory(),
            'rental_period_id' => RentalPeriod::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'is_active' => true,
        ];
    }
}