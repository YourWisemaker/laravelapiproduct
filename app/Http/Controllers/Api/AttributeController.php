<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $attributes = Attribute::with('values')->get();
        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }

    /**
     * Store a newly created attribute.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:attributes,name',
                'type' => 'required|string|in:text,number,boolean,select',
                'is_filterable' => 'sometimes|boolean',
                'is_required' => 'sometimes|boolean',
            ]);

            $attribute = Attribute::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attribute created successfully',
                'data' => $attribute
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
     * Display the specified attribute.
     *
     * @param Attribute $attribute
     * @return JsonResponse
     */
    public function show(Attribute $attribute): JsonResponse
    {
        $attribute->load('values');
        return response()->json([
            'success' => true,
            'data' => $attribute
        ]);
    }

    /**
     * Update the specified attribute.
     *
     * @param Request $request
     * @param Attribute $attribute
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Attribute $attribute): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255|unique:attributes,name,' . $attribute->id,
                'type' => 'sometimes|string|in:text,number,boolean,select',
                'is_filterable' => 'sometimes|boolean',
                'is_required' => 'sometimes|boolean',
            ]);

            $attribute->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attribute updated successfully',
                'data' => $attribute
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
     * Remove the specified attribute.
     *
     * @param Attribute $attribute
     * @return JsonResponse
     */
    public function destroy(Attribute $attribute): JsonResponse
    {
        // Check if attribute is used in products
        $inUse = $attribute->values()->whereHas('products')->exists();
        
        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete attribute that is in use by products.',
            ], 422);
        }
        
        $attribute->values()->delete();
        $attribute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute and all associated values deleted successfully'
        ]);
    }
}
