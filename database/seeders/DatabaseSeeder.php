<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Run seeders in the correct order
        $this->call([
            AttributeSeeder::class,    // First create attributes and their values
            RegionSeeder::class,       // Then create regions
            RentalPeriodSeeder::class, // Then create rental periods
            ProductSeeder::class,      // Then create products with attributes
            ProductPricingSeeder::class, // Finally create product pricing
        ]);
    }
}
