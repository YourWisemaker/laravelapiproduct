<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Color attribute
        $colorAttribute = Attribute::create([
            'name' => 'Color',
            'type' => 'select',
            'is_filterable' => true,
            'is_required' => true,
        ]);

        // Create Color values
        $colorValues = ['Red', 'Blue', 'Green', 'Black', 'White', 'Silver'];
        foreach ($colorValues as $value) {
            AttributeValue::create([
                'attribute_id' => $colorAttribute->id,
                'value' => $value,
            ]);
        }

        // Create Size attribute
        $sizeAttribute = Attribute::create([
            'name' => 'Size',
            'type' => 'select',
            'is_filterable' => true,
            'is_required' => true,
        ]);

        // Create Size values
        $sizeValues = ['Small', 'Medium', 'Large', 'X-Large'];
        foreach ($sizeValues as $value) {
            AttributeValue::create([
                'attribute_id' => $sizeAttribute->id,
                'value' => $value,
            ]);
        }

        // Create Material attribute
        $materialAttribute = Attribute::create([
            'name' => 'Material',
            'type' => 'select',
            'is_filterable' => true,
            'is_required' => false,
        ]);

        // Create Material values
        $materialValues = ['Cotton', 'Polyester', 'Leather', 'Metal', 'Plastic', 'Wood'];
        foreach ($materialValues as $value) {
            AttributeValue::create([
                'attribute_id' => $materialAttribute->id,
                'value' => $value,
            ]);
        }
    }
}