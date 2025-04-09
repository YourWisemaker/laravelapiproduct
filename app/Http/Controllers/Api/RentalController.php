<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Region;
use App\Models\RentalPeriod;
use App\Models\RentalTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RentalController extends Controller
{
    /**
     * Display a paginated list of rentals with optional filtering.
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/rentals",
     *     tags={"Rentals"},
     *     summary="Get list of rentals",
     *     description="Returns paginated list of rental transactions with optional filtering",
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filter by product ID",
     *         required=false,
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
     *         name="status",
     *         in="query",
     *         description="Filter by status (confirmed, cancelled, completed)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"confirmed", "cancelled", "completed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = RentalTransaction::with(['product', 'region', 'rentalPeriod']);
        
        // Filter by product if provided
        if ($request->has('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }
        
        // Filter by region if provided
        if ($request->has('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Get paginated results
        $rentals = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $rentals
        ]);
    }

    /**
     * Store a new rental transaction.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * 
     * @OA\Post(
     *     path="/api/v1/rentals",
     *     tags={"Rentals"},
     *     summary="Create new rental",
     *     description="Creates a new rental transaction with customer information",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "region_id", "rental_period_id", "customer_name", "customer_email", "customer_address", "start_date"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="region_id", type="integer", example=1),
     *             @OA\Property(property="rental_period_id", type="integer", example=1),
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="customer_email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="customer_address", type="string", example="123 Main St, City, Country"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-04-15"),
     *             @OA\Property(property="notes", type="string", example="Please deliver in the morning", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rental created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product rental created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or product not available for rental"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'region_id' => 'required|exists:regions,id',
            'rental_period_id' => 'required|exists:rental_periods,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'customer_address' => 'required|string',
            'notes' => 'sometimes|string|nullable',
        ]);

        // Get the product, region, and rental period
        $product = Product::findOrFail($validated['product_id']);
        $region = Region::findOrFail($validated['region_id']);
        $rentalPeriod = RentalPeriod::findOrFail($validated['rental_period_id']);
        
        // Verify the product is active
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This product is not available for rental',
            ], 422);
        }
        
        // Get pricing for this product, region, and rental period
        $pricing = $product->pricing()
            ->where('region_id', $region->id)
            ->where('rental_period_id', $rentalPeriod->id)
            ->where('is_active', true)
            ->first();
            
        if (!$pricing) {
            return response()->json([
                'success' => false,
                'message' => 'No pricing available for this product in the selected region and rental period',
            ], 422);
        }

        // Calculate end date based on start date and rental period days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addDays($rentalPeriod->days);
        
        // Create the rental transaction
        $rental = new RentalTransaction();
        $rental->product_id = $product->id;
        $rental->region_id = $region->id;
        $rental->rental_period_id = $rentalPeriod->id;
        $rental->customer_name = $validated['customer_name'];
        $rental->customer_email = $validated['customer_email'];
        $rental->customer_address = $validated['customer_address'];
        $rental->start_date = $startDate;
        $rental->end_date = $endDate;
        $rental->price = $pricing->price;
        $rental->status = 'confirmed';
        $rental->notes = $validated['notes'] ?? null;
        $rental->save();
        
        // Load relationships for response
        $rental->load(['product', 'region', 'rentalPeriod']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product rental created successfully',
            'data' => $rental
        ], 201);
    }

    /**
     * Display a specific rental transaction.
     *
     * @param RentalTransaction $rental
     * @return JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/rentals/{rental}",
     *     tags={"Rentals"},
     *     summary="Get rental details",
     *     description="Returns detailed information about a specific rental transaction",
     *     @OA\Parameter(
     *         name="rental",
     *         in="path",
     *         description="Rental ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rental not found"
     *     )
     * )
     */
    public function show(RentalTransaction $rental): JsonResponse
    {
        $rental->load(['product', 'region', 'rentalPeriod']);
        
        return response()->json([
            'success' => true,
            'data' => $rental
        ]);
    }

    /**
     * Update a rental transaction.
     *
     * @param Request $request
     * @param RentalTransaction $rental
     * @return JsonResponse
     * @throws ValidationException
     * 
     * @OA\Put(
     *     path="/api/v1/rentals/{rental}",
     *     tags={"Rentals"},
     *     summary="Update rental",
     *     description="Updates an existing rental transaction",
     *     @OA\Parameter(
     *         name="rental",
     *         in="path",
     *         description="Rental ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_name", type="string", example="John Smith"),
     *             @OA\Property(property="customer_email", type="string", format="email"),
     *             @OA\Property(property="customer_address", type="string"),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"confirmed", "cancelled", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rental updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rental not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, RentalTransaction $rental): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'customer_address' => 'sometimes|string',
            'notes' => 'sometimes|string|nullable',
            'status' => 'sometimes|in:confirmed,cancelled,completed',
        ]);

        // Update fields
        $rental->fill($validated);
        $rental->save();
        
        // Load relationships for response
        $rental->load(['product', 'region', 'rentalPeriod']);
        
        return response()->json([
            'success' => true,
            'message' => 'Rental updated successfully',
            'data' => $rental
        ]);
    }
}
