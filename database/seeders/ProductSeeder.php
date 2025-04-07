<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create products
        $products = [
            [
                'name' => 'Professional Camera',
                'description' => 'High-end DSLR camera for professional photography',
                'sku' => 'CAM-PRO-001',
                'attributes' => [
                    'Color' => 'Black',
                    'Size' => 'Medium',
                    'Material' => 'Metal'
                ]
            ],
            [
                'name' => 'Drone',
                'description' => 'Aerial photography drone with 4K camera',
                'sku' => 'DRN-4K-002',
                'attributes' => [
                    'Color' => 'White',
                    'Size' => 'Medium',
                    'Material' => 'Plastic'
                ]
            ],
            [
                'name' => 'Projector',
                'description' => 'HD projector for presentations and home cinema',
                'sku' => 'PRJ-HD-003',
                'attributes' => [
                    'Color' => 'Black',
                    'Size' => 'Small',
                    'Material' => 'Plastic'
                ]
            ],
            [
                'name' => 'Audio Mixer',
                'description' => 'Professional audio mixer for studio recording',
                'sku' => 'AUD-MIX-004',
                'attributes' => [
                    'Color' => 'Silver',
                    'Size' => 'Large',
                    'Material' => 'Metal'
                ]
            ],
            [
                'name' => 'Lighting Kit',
                'description' => 'Professional lighting kit for photography and videography',
                'sku' => 'LGT-KIT-005',
                'attributes' => [
                    'Color' => 'Black',
                    'Size' => 'Large',
                    'Material' => 'Metal'
                ]
            ],
        ];

        foreach ($products as $productData) {
            // Create the product
            $product = Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'sku' => $productData['sku'],
                'is_active' => true,
            ]);

            // Attach attribute values
            foreach ($productData['attributes'] as $attributeName => $attributeValue) {
                $attrValue = AttributeValue::whereHas('attribute', function ($query) use ($attributeName) {
                    $query->where('name', $attributeName);
                })->where('value', $attributeValue)->first();

                if ($attrValue) {
                    $product->attributeValues()->attach($attrValue->id);
                }
            }
        }
    }
}