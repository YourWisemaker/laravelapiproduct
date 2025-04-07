<?php

namespace Database\Seeders;

use App\Models\RentalPeriod;
use Illuminate\Database\Seeder;

class RentalPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create rental periods
        $rentalPeriods = [
            ['name' => 'Daily', 'days' => 1],
            ['name' => 'Weekly', 'days' => 7],
            ['name' => 'Bi-Weekly', 'days' => 14],
            ['name' => 'Monthly', 'days' => 30],
            ['name' => '3 Months', 'days' => 90],  // Quarterly - 3 months
            ['name' => '6 Months', 'days' => 180], // Half-Yearly - 6 months
            ['name' => '12 Months', 'days' => 365], // Yearly - 12 months
        ];

        foreach ($rentalPeriods as $period) {
            RentalPeriod::create([
                'name' => $period['name'],
                'days' => $period['days'],
                'is_active' => true,
            ]);
        }
    }
}