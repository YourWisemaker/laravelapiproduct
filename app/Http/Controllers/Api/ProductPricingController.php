<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Region;
use App\Models\RentalPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductPricingController extends Controller
{
    /**
     * Display a listing of product pricing.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function index(Product $product): JsonResponse
    {
        $pricing = $product->pricing()
            ->with(['region', 'rentalPeriod'])
            ->get();
            
        return response()->json(['data' => $pricing]);
    }

    /**
     * Store new product pricing.
     *
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'rental_period_id' => 'required|exists:rental_periods,id',
            'price' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Check if pricing already exists for this combination
        $exists = $product->pricing()
            ->where('region_id', $validated['region_id'])
            ->where('rental_period_id', $validated['rental_period_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Pricing for this region and rental period already exists',
            ], 422);
        }

        $validated['product_id'] = $product->id;
        $pricing = ProductPricing::create($validated);
        
        // Load relationships for response
        $pricing->load(['region', 'rentalPeriod']);

        return response()->json([
            'message' => 'Product pricing created successfully',
            'data' => $pricing
        ], 201);
    }

    /**
     * Display the specified product pricing.
     *
     * @param Product $product
     * @param ProductPricing $pricing
     * @return JsonResponse
     */
    public function show(Product $product, ProductPricing $pricing): JsonResponse
    {
        // Ensure the pricing belongs to the product
        if ($pricing->product_id !== $product->id) {
            return response()->json([
                'message' => 'Pricing does not belong to this product',
            ], 404);
        }

        $pricing->load(['region', 'rentalPeriod']);
        return response()->json(['data' => $pricing]);
    }

    /**
     * Update the specified product pricing.
     *
     * @param Request $request
     * @param Product $product
     * @param ProductPricing $pricing
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Product $product, ProductPricing $pricing): JsonResponse
    {
        // Ensure the pricing belongs to the product
        if ($pricing->product_id !== $product->id) {
            return response()->json([
                'message' => 'Pricing does not belong to this product',
            ], 404);
        }

        $validated = $request->validate([
            'region_id' => 'sometimes|exists:regions,id',
            'rental_period_id' => 'sometimes|exists:rental_periods,id',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // If region or rental period is changing, check for duplicate
        if ((isset($validated['region_id']) && $validated['region_id'] != $pricing->region_id) || 
            (isset($validated['rental_period_id']) && $validated['rental_period_id'] != $pricing->rental_period_id)) {
            
            $regionId = $validated['region_id'] ?? $pricing->region_id;
            $rentalPeriodId = $validated['rental_period_id'] ?? $pricing->rental_period_id;
            
            $exists = $product->pricing()
                ->where('id', '!=', $pricing->id)
                ->where('region_id', $regionId)
                ->where('rental_period_id', $rentalPeriodId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Pricing for this region and rental period already exists',
                ], 422);
            }
        }

        $pricing->update($validated);
        $pricing->load(['region', 'rentalPeriod']);

        return response()->json([
            'message' => 'Product pricing updated successfully',
            'data' => $pricing
        ]);
    }

    /**
     * Remove the specified product pricing.
     *
     * @param Product $product
     * @param ProductPricing $pricing
     * @return JsonResponse
     */
    public function destroy(Product $product, ProductPricing $pricing): JsonResponse
    {
        // Ensure the pricing belongs to the product
        if ($pricing->product_id !== $product->id) {
            return response()->json([
                'message' => 'Pricing does not belong to this product',
            ], 404);
        }

        $pricing->delete();

        return response()->json([
            'message' => 'Product pricing deleted successfully'
        ]);
    }
}
