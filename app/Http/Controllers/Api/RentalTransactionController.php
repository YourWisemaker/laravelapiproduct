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

class RentalTransactionController extends Controller
{
    /**
     * Display a listing of rental transactions.
     *
     * @param Request $request
     * @return JsonResponse
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
        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Store a newly created rental transaction.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
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
        $transaction = new RentalTransaction();
        $transaction->product_id = $product->id;
        $transaction->region_id = $region->id;
        $transaction->rental_period_id = $rentalPeriod->id;
        $transaction->customer_name = $validated['customer_name'];
        $transaction->customer_email = $validated['customer_email'];
        $transaction->customer_address = $validated['customer_address'];
        $transaction->start_date = $startDate;
        $transaction->end_date = $endDate;
        $transaction->price = $pricing->price;
        $transaction->status = 'confirmed';
        $transaction->notes = $validated['notes'] ?? null;
        $transaction->save();
        
        // Load relationships for response
        $transaction->load(['product', 'region', 'rentalPeriod']);
        
        return response()->json([
            'success' => true,
            'message' => 'Rental transaction created successfully',
            'data' => $transaction
        ], 201);
    }

    /**
     * Display the specified rental transaction.
     *
     * @param RentalTransaction $transaction
     * @return JsonResponse
     */
    public function show(RentalTransaction $transaction): JsonResponse
    {
        $transaction->load(['product', 'region', 'rentalPeriod']);
        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Update the specified rental transaction.
     *
     * @param Request $request
     * @param RentalTransaction $transaction
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, RentalTransaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'customer_address' => 'sometimes|string',
            'notes' => 'sometimes|string|nullable',
            'status' => 'sometimes|in:confirmed,cancelled,completed',
        ]);

        // If changing start date, recalculate end date
        if (isset($validated['start_date'])) {
            $startDate = Carbon::parse($validated['start_date']);
            $transaction->start_date = $startDate;
            $transaction->end_date = $startDate->copy()->addDays($transaction->rentalPeriod->days);
        }
        
        // Update other fields
        $transaction->fill($validated);
        $transaction->save();
        
        // Load relationships for response
        $transaction->load(['product', 'region', 'rentalPeriod']);
        
        return response()->json([
            'success' => true,
            'message' => 'Rental transaction updated successfully',
            'data' => $transaction
        ]);
    }

    /**
     * Remove the specified rental transaction.
     *
     * @param RentalTransaction $transaction
     * @return JsonResponse
     */
    public function destroy(RentalTransaction $transaction): JsonResponse
    {
        // If the transaction is in 'confirmed' status and hasn't started yet, 
        // it can be deleted; otherwise, it should be marked as cancelled
        $now = Carbon::now();
        if ($transaction->status === 'confirmed' && $transaction->start_date->isAfter($now)) {
            $transaction->delete();
            return response()->json([
                'success' => true,
                'message' => 'Rental transaction deleted successfully'
            ]);
        } else {
            $transaction->status = 'cancelled';
            $transaction->save();
            return response()->json([
                'success' => true,
                'message' => 'Rental transaction cancelled successfully',
                'data' => $transaction
            ]);
        }
    }
}
