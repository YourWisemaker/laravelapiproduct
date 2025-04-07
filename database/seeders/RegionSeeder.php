<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regions
        $regions = [
            ['name' => 'North America', 'code' => 'NA'],
            ['name' => 'Europe', 'code' => 'EU'],
            ['name' => 'Asia Pacific', 'code' => 'APAC'],
            ['name' => 'Middle East', 'code' => 'ME'],
            ['name' => 'Africa', 'code' => 'AF'],
            ['name' => 'Latin America', 'code' => 'LATAM'],
        ];

        foreach ($regions as $region) {
            Region::create([
                'name' => $region['name'],
                'code' => $region['code'],
                'is_active' => true,
            ]);
        }
    }
}