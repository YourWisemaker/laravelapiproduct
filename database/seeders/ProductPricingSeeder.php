<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Region;
use App\Models\RentalPeriod;
use App\Models\ProductPricing;
use Illuminate\Database\Seeder;

class ProductPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products, regions, and rental periods
        $products = Product::all();
        $regions = Region::all();
        $rentalPeriods = RentalPeriod::all();

        // Base prices for each product (daily rate in USD)
        $basePrices = [
            'CAM-PRO-001' => 50.00,  // Professional Camera
            'DRN-4K-002' => 75.00,   // Drone
            'PRJ-HD-003' => 40.00,   // Projector
            'AUD-MIX-004' => 60.00,  // Audio Mixer
            'LGT-KIT-005' => 45.00,  // Lighting Kit
        ];

        // Regional price multipliers (relative to base price)
        $regionMultipliers = [
            'NA' => 1.0,     // North America (base)
            'EU' => 1.2,     // Europe (20% higher)
            'APAC' => 0.9,   // Asia Pacific (10% lower)
            'ME' => 1.3,     // Middle East (30% higher)
            'AF' => 0.8,     // Africa (20% lower)
            'LATAM' => 0.85, // Latin America (15% lower)
        ];

        // Create pricing for each product in each region for each rental period
        foreach ($products as $product) {
            $basePrice = $basePrices[$product->sku] ?? 50.00; // Default if not found
            
            foreach ($regions as $region) {
                $regionMultiplier = $regionMultipliers[$region->code] ?? 1.0;
                
                foreach ($rentalPeriods as $rentalPeriod) {
                    // Calculate price based on rental period length with volume discount
                    // Longer rental periods get better daily rates
                    $periodMultiplier = match(true) {
                        $rentalPeriod->days >= 180 => 0.5,  // Half-yearly: 50% of daily rate
                        $rentalPeriod->days >= 90 => 0.6,   // Quarterly: 60% of daily rate
                        $rentalPeriod->days >= 30 => 0.7,   // Monthly: 70% of daily rate
                        $rentalPeriod->days >= 14 => 0.8,   // Bi-Weekly: 80% of daily rate
                        $rentalPeriod->days >= 7 => 0.9,    // Weekly: 90% of daily rate
                        default => 1.0                      // Daily: full rate
                    };
                    
                    // Calculate final price
                    $price = $basePrice * $regionMultiplier * $rentalPeriod->days * $periodMultiplier;
                    
                    // Round to 2 decimal places
                    $price = round($price, 2);
                    
                    // Create the pricing record
                    ProductPricing::create([
                        'product_id' => $product->id,
                        'region_id' => $region->id,
                        'rental_period_id' => $rentalPeriod->id,
                        'price' => $price,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}