<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Region;
use App\Models\RentalPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Product Rental API",
 *     version="1.0.0",
 *     description="API for managing product rentals with regional pricing",
 *     @OA\Contact(
 *         email="contact@example.com",
 *         name="API Support"
 *     )
 * )
 */

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     description="Returns paginated list of products with optional filtering by region and rental period",
     *     operationId="getProducts",
     *     @OA\Parameter(
     *         name="region_id",
     *         in="query",
     *         description="Filter by region ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="rental_period_id",
     *         in="query",
     *         description="Filter by rental period ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="sku", type="string"),
     *                 @OA\Property(property="is_active", type="boolean"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'region_id' => 'sometimes|integer|exists:regions,id',
            'rental_period_id' => 'sometimes|integer|exists:rental_periods,id',
        ]);
    
        $region = isset($validatedData['region_id']) ? Region::find($validatedData['region_id']) : null;
        $rentalPeriod = isset($validatedData['rental_period_id']) ? RentalPeriod::find($validatedData['rental_period_id']) : null;
    
        if ($region === null && $request->has('region_id')) {
            return response()->json(['error' => 'Invalid region ID'], 422);
        }
    
        if ($rentalPeriod === null && $request->has('rental_period_id')) {
            return response()->json(['error' => 'Invalid rental period ID'], 422);
        }
    
        $query = Product::where('is_active', true)
            ->with(['attributeValues.attribute']);
    
        $this->applyPricingFilters($query, $validatedData);
    
        $products = $query->paginate(15);
    
        return response()->json($products);
    }
    
    private function applyPricingFilters($query, array $validatedData): void
    {
        if (!empty($validatedData['region_id']) || !empty($validatedData['rental_period_id'])) {
            $query->whereHas('pricing', function ($pricingQuery) use ($validatedData) {
                $pricingQuery->where('is_active', true);
    
                if (!empty($validatedData['region_id'])) {
                    $pricingQuery->where('region_id', $validatedData['region_id']);
                }
    
                if (!empty($validatedData['rental_period_id'])) {
                    $pricingQuery->where('rental_period_id', $validatedData['rental_period_id']);
                }
            });
    
            $query->with(['pricing' => function ($pricingQuery) use ($validatedData) {
                $pricingQuery->where('is_active', true);
    
                if (!empty($validatedData['region_id'])) {
                    $pricingQuery->where('region_id', $validatedData['region_id']);
                }
    
                if (!empty($validatedData['rental_period_id'])) {
                    $pricingQuery->where('rental_period_id', $validatedData['rental_period_id']);
                }
    
                $pricingQuery->with(['region', 'rentalPeriod']);
            }]);
        }
    }
    
    /**
     * Display the specified product with pricing information.
     * 
     * @OA\Get(
     *     path="/api/v1/products/{product}",
     *     tags={"Products"},
     *     summary="Get product details",
     *     description="Returns detailed information about a specific product",
     *     operationId="getProductDetail",
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="region_id",
     *         in="query",
     *         description="Region ID for pricing",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="sku", type="string"),
     *                 @OA\Property(property="is_active", type="boolean"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="region", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="is_active", type="boolean"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        // Get region from request or default to first active region
        $regionId = $request->query('region_id');
        $region = $regionId ? Region::find($regionId) : Region::where('is_active', true)->first();
    
        if (!$region) {
            return response()->json(['error' => 'Invalid region ID'], 422);
        }
    
        // Load the product with its attributes and pricing for the specified region
        $product->load([
            'attributeValues.attribute',
            'pricing' => function ($query) use ($region) {
                $query->where('region_id', $region->id)
                      ->where('is_active', true)
                      ->with('rentalPeriod');
            }
        ]);
    
        return response()->json([
            'product' => $product,
            'region' => $region,
        ]);
    }
    
    /**
     * Get pricing for a product based on region and rental period
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/products/{product}/pricing",
     *     tags={"Product Pricing"},
     *     summary="Get product pricing",
     *     description="Returns pricing information for a specific product with optional filtering",
     *     operationId="getProductPricing",
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="region_id",
     *         in="query",
     *         description="Filter by region ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="rental_period_id",
     *         in="query",
     *         description="Filter by rental period ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", 
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="region", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string")
     *                 ),
     *                 @OA\Property(property="rental_period", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="days", type="integer")
     *                 ),
     *                 @OA\Property(property="price", type="number")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function getPricing(Request $request, Product $product): JsonResponse
    {
        $validatedData = $request->validate([
            'region_id' => 'sometimes|exists:regions,id',
            'rental_period_id' => 'sometimes|exists:rental_periods,id',
        ]);
    
        $region = isset($validatedData['region_id']) ? Region::find($validatedData['region_id']) : null;
        $rentalPeriod = isset($validatedData['rental_period_id']) ? RentalPeriod::find($validatedData['rental_period_id']) : null;
    
        if ($region === null && $request->has('region_id')) {
            return response()->json(['error' => 'Invalid region ID'], 422);
        }
    
        if ($rentalPeriod === null && $request->has('rental_period_id')) {
            return response()->json(['error' => 'Invalid rental period ID'], 422);
        }
    
        $query = $product->pricing()->where('is_active', true);
        
        // Apply region filter if provided
        if (isset($validatedData['region_id'])) {
            $query->where('region_id', $validatedData['region_id']);
        }
        
        // Apply rental period filter if provided
        if (isset($validatedData['rental_period_id'])) {
            $query->where('rental_period_id', $validatedData['rental_period_id']);
        }
        
        $pricing = $query->with(['region', 'rentalPeriod'])
            ->get()
            ->map(function ($price) {
                return [
                    'id' => $price->id,
                    'region' => [
                        'id' => $price->region->id,
                        'name' => $price->region->name,
                        'code' => $price->region->code,
                    ],
                    'rental_period' => [
                        'id' => $price->rentalPeriod->id,
                        'name' => $price->rentalPeriod->name,
                        'days' => $price->rentalPeriod->days,
                    ],
                    'price' => $price->price,
                ];
            });
    
        return response()->json([
            'success' => true,
            'data' => $pricing,
        ]);
    }

    /**
     * Get all active regions.
     * 
     * @OA\Get(
     *     path="/api/v1/regions",
     *     tags={"Reference Data"},
     *     summary="Get all active regions",
     *     description="Returns a list of all active regions/countries",
     *     operationId="getRegions",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         ))
     *     )
     * )
     */
    public function regions(): JsonResponse
    {
        $regions = Region::where('is_active', true)->get();
        return response()->json($regions);
    }

    /**
     * Get all active rental periods.
     * 
     * @OA\Get(
     *     path="/api/v1/rental-periods",
     *     tags={"Reference Data"},
     *     summary="Get all active rental periods",
     *     description="Returns a list of all active rental periods",
     *     operationId="getRentalPeriods",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="days", type="integer"),
     *             @OA\Property(property="is_active", type="boolean")
     *         ))
     *     )
     * )
     */
    public function rentalPeriods(): JsonResponse
    {
        $rentalPeriods = RentalPeriod::where('is_active', true)
            ->orderBy('days')
            ->get();
            
        return response()->json($rentalPeriods);
    }
}