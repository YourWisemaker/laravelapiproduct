<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of attribute values.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = AttributeValue::with('attribute');
        
        // Filter by attribute_id if provided
        if ($request->has('attribute_id')) {
            $query->where('attribute_id', $request->attribute_id);
        }
        
        $attributeValues = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $attributeValues
        ]);
    }

    /**
     * Store a newly created attribute value.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'attribute_id' => 'required|exists:attributes,id',
                'value' => 'required|string|max:255',
            ]);

            // Check for uniqueness of value within the same attribute
            $exists = AttributeValue::where('attribute_id', $validated['attribute_id'])
                ->where('value', $validated['value'])
                ->exists();
                
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'The value already exists for this attribute.'
                ], 422);
            }

            $attributeValue = AttributeValue::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attribute value created successfully',
                'data' => $attributeValue
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
     * Display the specified attribute value.
     *
     * @param AttributeValue $attributeValue
     * @return JsonResponse
     */
    public function show(AttributeValue $attributeValue): JsonResponse
    {
        $attributeValue->load('attribute');
        
        return response()->json([
            'success' => true,
            'data' => $attributeValue
        ]);
    }

    /**
     * Update the specified attribute value.
     *
     * @param Request $request
     * @param AttributeValue $attributeValue
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, AttributeValue $attributeValue): JsonResponse
    {
        try {
            $validated = $request->validate([
                'value' => 'sometimes|string|max:255',
            ]);

            // Check for uniqueness of value within the same attribute
            $exists = AttributeValue::where('attribute_id', $attributeValue->attribute_id)
                ->where('value', $validated['value'])
                ->where('id', '!=', $attributeValue->id)
                ->exists();
                
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'The value already exists for this attribute.'
                ], 422);
            }

            $attributeValue->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attribute value updated successfully',
                'data' => $attributeValue
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
     * Remove the specified attribute value.
     *
     * @param AttributeValue $attributeValue
     * @return JsonResponse
     */
    public function destroy(AttributeValue $attributeValue): JsonResponse
    {
        // Check if attribute value is used in products
        $inUse = $attributeValue->products()->exists();
        
        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete attribute value that is in use by products.',
            ], 422);
        }
        
        $attributeValue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute value deleted successfully'
        ]);
    }
}
