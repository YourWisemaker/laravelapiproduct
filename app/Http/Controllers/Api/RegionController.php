<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegionController extends Controller
{
    /**
     * Display a listing of regions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $regions = Region::all();
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    /**
     * Store a newly created region.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:regions,code',
            'is_active' => 'sometimes|boolean',
        ]);

        $region = Region::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Region created successfully',
            'data' => $region
        ], 201);
    }

    /**
     * Display the specified region.
     *
     * @param Region $region
     * @return JsonResponse
     */
    public function show(Region $region): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }

    /**
     * Update the specified region.
     *
     * @param Request $request
     * @param Region $region
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Region $region): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10|unique:regions,code,' . $region->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $region->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Region updated successfully',
            'data' => $region
        ]);
    }

    /**
     * Remove the specified region.
     *
     * @param Region $region
     * @return JsonResponse
     */
    public function destroy(Region $region): JsonResponse
    {
        // Check if region is used in product pricing
        $inUse = $region->productPricing()->exists();
        
        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete region that is in use. Consider deactivating it instead.',
            ], 422);
        }
        
        $region->delete();

        return response()->json([
            'success' => true,
            'message' => 'Region deleted successfully'
        ]);
    }
}
