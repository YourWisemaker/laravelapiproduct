<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Region;
use App\Models\RentalPeriod;
use App\Models\ProductPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test data
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Test products index endpoint returns products
     */
    public function test_products_index_returns_products(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);
        
        // Assert we have products in the response
        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Test products can be filtered by region
     */
    public function test_products_can_be_filtered_by_region(): void
    {
        $region = Region::first();
        
        $response = $this->getJson("/api/v1/products?region_id={$region->id}");

        $response->assertStatus(200);
        
        // Get the products that should have pricing for this region
        $productsWithRegionPricing = Product::whereHas('pricing', function ($query) use ($region) {
            $query->where('region_id', $region->id);
        })->count();
        
        // Assert that the number of products returned matches what we expect
        $this->assertEquals($productsWithRegionPricing, $response->json('total'));
    }

    /**
     * Test products can be filtered by rental period
     */
    public function test_products_can_be_filtered_by_rental_period(): void
    {
        $rentalPeriod = RentalPeriod::first();
        
        $response = $this->getJson("/api/v1/products?rental_period_id={$rentalPeriod->id}");

        $response->assertStatus(200);
        
        // Get the products that should have pricing for this rental period
        $productsWithRentalPeriodPricing = Product::whereHas('pricing', function ($query) use ($rentalPeriod) {
            $query->where('rental_period_id', $rentalPeriod->id);
        })->count();
        
        // Assert that the number of products returned matches what we expect
        $this->assertEquals($productsWithRentalPeriodPricing, $response->json('total'));
    }

    /**
     * Test products can be filtered by both region and rental period
     */
    public function test_products_can_be_filtered_by_region_and_rental_period(): void
    {
        $region = Region::first();
        $rentalPeriod = RentalPeriod::first();
        
        $response = $this->getJson("/api/v1/products?region_id={$region->id}&rental_period_id={$rentalPeriod->id}");

        $response->assertStatus(200);
        
        // Get the products that should have pricing for this region and rental period
        $productsWithBothFilters = Product::whereHas('pricing', function ($query) use ($region, $rentalPeriod) {
            $query->where('region_id', $region->id)
                  ->where('rental_period_id', $rentalPeriod->id);
        })->count();
        
        // Assert that the number of products returned matches what we expect
        $this->assertEquals($productsWithBothFilters, $response->json('total'));
    }

    /**
     * Test product detail endpoint returns correct data
     */
    public function test_product_detail_returns_correct_data(): void
    {
        $product = Product::first();
        
        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'product' => [
                'id',
                'name',
                'description',
                'sku',
                'is_active',
                'created_at',
                'updated_at',
                'attribute_values',
                'pricing',
            ],
            'region' => [
                'id',
                'name',
                'code',
                'is_active',
            ],
        ]);
        
        // Assert the product ID matches
        $this->assertEquals($product->id, $response->json('product.id'));
    }

    /**
     * Test product pricing endpoint returns correct data
     */
    public function test_product_pricing_returns_correct_data(): void
    {
        $product = Product::first();
        
        $response = $this->getJson("/api/v1/products/{$product->id}/pricing");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'region',
                    'rental_period',
                    'price',
                ],
            ],
        ]);
        
        // Assert success is true
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test product pricing can be filtered by region
     */
    public function test_product_pricing_can_be_filtered_by_region(): void
    {
        $product = Product::first();
        $region = Region::first();
        
        $response = $this->getJson("/api/v1/products/{$product->id}/pricing?region_id={$region->id}");

        $response->assertStatus(200);
        
        // Check that all returned pricing items have the correct region
        foreach ($response->json('data') as $pricing) {
            $this->assertEquals($region->id, $pricing['region']['id']);
        }
    }

    /**
     * Test product pricing can be filtered by rental period
     */
    public function test_product_pricing_can_be_filtered_by_rental_period(): void
    {
        $product = Product::first();
        $rentalPeriod = RentalPeriod::first();
        
        $response = $this->getJson("/api/v1/products/{$product->id}/pricing?rental_period_id={$rentalPeriod->id}");

        $response->assertStatus(200);
        
        // Check that all returned pricing items have the correct rental period
        foreach ($response->json('data') as $pricing) {
            $this->assertEquals($rentalPeriod->id, $pricing['rental_period']['id']);
        }
    }

    /**
     * Test validation for invalid region ID
     */
    public function test_validation_for_invalid_region_id(): void
    {
        $response = $this->getJson("/api/v1/products?region_id=999999");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['region_id']);
    }

    /**
     * Test validation for invalid rental period ID
     */
    public function test_validation_for_invalid_rental_period_id(): void
    {
        $response = $this->getJson("/api/v1/products?rental_period_id=999999");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rental_period_id']);
      }

    /**
     * Create test data for the tests
     */
    private function createTestData(): void
    {
        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'is_active' => true,
        ]);

        // Create regions
        $region1 = Region::factory()->create([
            'name' => 'North America',
            'code' => 'NA',
            'is_active' => true,
        ]);

        $region2 = Region::factory()->create([
            'name' => 'Europe',
            'code' => 'EU',
            'is_active' => true,
        ]);

        // Create rental periods
        $rentalPeriod1 = RentalPeriod::factory()->create([
            'name' => 'Daily',
            'days' => 1,
            'is_active' => true,
        ]);

        $rentalPeriod2 = RentalPeriod::factory()->create([
            'name' => 'Weekly',
            'days' => 7,
            'is_active' => true,
        ]);

        // Create product pricing
        ProductPricing::factory()->create([
            'product_id' => $product->id,
            'region_id' => $region1->id,
            'rental_period_id' => $rentalPeriod1->id,
            'price' => 50.00,
            'is_active' => true,
        ]);

        ProductPricing::factory()->create([
            'product_id' => $product->id,
            'region_id' => $region1->id,
            'rental_period_id' => $rentalPeriod2->id,
            'price' => 300.00,
            'is_active' => true,
        ]);

        ProductPricing::factory()->create([
            'product_id' => $product->id,
            'region_id' => $region2->id,
            'rental_period_id' => $rentalPeriod1->id,
            'price' => 60.00,
            'is_active' => true,
        ]);

        ProductPricing::factory()->create([
            'product_id' => $product->id,
            'region_id' => $region2->id,
            'rental_period_id' => $rentalPeriod2->id,
            'price' => 350.00,
            'is_active' => true,
        ]);
    }
}