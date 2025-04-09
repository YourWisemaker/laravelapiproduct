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

class PricingController extends Controller
{
    /**
     * Store a new product pricing.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * 
     * @OA\Post(
     *     path="/api/v1/pricing",
     *     tags={"Pricing"},
     *     summary="Create new product pricing",
     *     description="Creates a new pricing for a product-region-period combination",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "region_id", "rental_period_id", "price"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="region_id", type="integer", example=1),
     *             @OA\Property(property="rental_period_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product pricing created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product pricing created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or pricing already exists"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'region_id' => 'required|exists:regions,id',
            'rental_period_id' => 'required|exists:rental_periods,id',
            'price' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Check if pricing already exists for this combination
        $exists = ProductPricing::where('product_id', $validated['product_id'])
            ->where('region_id', $validated['region_id'])
            ->where('rental_period_id', $validated['rental_period_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Pricing for this product-region-period combination already exists',
            ], 422);
        }

        $pricing = ProductPricing::create($validated);
        
        // Load relationships for response
        $pricing->load(['region', 'rentalPeriod', 'product']);

        return response()->json([
            'success' => true,
            'message' => 'Product pricing created successfully',
            'data' => $pricing
        ], 201);
    }

    /**
     * Update an existing product pricing.
     *
     * @param Request $request
     * @param ProductPricing $pricing
     * @return JsonResponse
     * @throws ValidationException
     * 
     * @OA\Put(
     *     path="/api/v1/pricing/{pricing}",
     *     tags={"Pricing"},
     *     summary="Update product pricing",
     *     description="Updates the price and active status of an existing pricing",
     *     @OA\Parameter(
     *         name="pricing",
     *         in="path",
     *         description="Pricing ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="price", type="number", format="float", example=129.99),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product pricing updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product pricing updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pricing not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, ProductPricing $pricing): JsonResponse
    {
        $validated = $request->validate([
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $pricing->update($validated);
        $pricing->load(['region', 'rentalPeriod', 'product']);

        return response()->json([
            'success' => true,
            'message' => 'Product pricing updated successfully',
            'data' => $pricing
        ]);
    }

    /**
     * Remove a product pricing.
     *
     * @param ProductPricing $pricing
     * @return JsonResponse
     * 
     * @OA\Delete(
     *     path="/api/v1/pricing/{pricing}",
     *     tags={"Pricing"},
     *     summary="Delete product pricing",
     *     description="Deletes an existing product pricing",
     *     @OA\Parameter(
     *         name="pricing",
     *         in="path",
     *         description="Pricing ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product pricing deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product pricing deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pricing not found"
     *     )
     * )
     */
    public function destroy(ProductPricing $pricing): JsonResponse
    {
        $pricing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product pricing deleted successfully'
        ]);
    }
}
