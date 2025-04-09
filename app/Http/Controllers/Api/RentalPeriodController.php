<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RentalPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationException; // This line was already present, no change needed

class RentalPeriodController extends Controller
{
    /**
     * Display a listing of rental periods.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $rentalPeriods = RentalPeriod::orderBy('days')->get();
        return response()->json([
            'success' => true,
            'data' => $rentalPeriods
        ]);
    }

    /**
     * Store a newly created rental period.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'days' => 'required|integer|min:1',
                'is_active' => 'sometimes|boolean',
            ]);

            $rentalPeriod = RentalPeriod::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Rental period created successfully',
                'data' => $rentalPeriod
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified rental period.
     *
     * @param RentalPeriod $rentalPeriod
     * @return JsonResponse
     */
    public function show(RentalPeriod $rentalPeriod): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $rentalPeriod
        ]);
    }

    /**
     * Update the specified rental period.
     *
     * @param Request $request
     * @param RentalPeriod $rentalPeriod
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, RentalPeriod $rentalPeriod): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'days' => 'sometimes|integer|min:1|unique:rental_periods,days,' . $rentalPeriod->id,
                'is_active' => 'sometimes|boolean',
            ]);

            $rentalPeriod->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Rental period updated successfully',
                'data' => $rentalPeriod
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified rental period.
     *
     * @param RentalPeriod $rentalPeriod
     * @return JsonResponse
     */
    public function destroy(RentalPeriod $rentalPeriod): JsonResponse
    {
        // Check if rental period is used in product pricing
        $inUse = $rentalPeriod->productPricing()->exists();
        
        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete rental period that is in use. Consider deactivating it instead.',
            ], 422);
        }
        
        $rentalPeriod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rental period deleted successfully'
        ]);
    }
}
