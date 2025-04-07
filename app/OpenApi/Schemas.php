<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"id", "name", "description", "sku", "is_active"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Premium Camera"),
 *     @OA\Property(property="description", type="string", example="High-quality camera for professional photography"),
 *     @OA\Property(property="sku", type="string", example="CAM-001"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProductDetail",
 *     @OA\Property(property="product", ref="#/components/schemas/Product"),
 *     @OA\Property(
 *         property="region",
 *         ref="#/components/schemas/Region"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProductCollection",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/v1/products?page=1"),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=1),
 *     @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/v1/products?page=1"),
 *     @OA\Property(
 *         property="links",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="url", type="string", nullable=true),
 *             @OA\Property(property="label", type="string"),
 *             @OA\Property(property="active", type="boolean")
 *         )
 *     ),
 *     @OA\Property(property="next_page_url", type="string", nullable=true),
 *     @OA\Property(property="path", type="string", example="http://localhost:8000/api/v1/products"),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=50)
 * )
 */

/**
 * @OA\Schema(
 *     schema="Region",
 *     required={"id", "name", "code", "is_active"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="North America"),
 *     @OA\Property(property="code", type="string", example="NA"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="RentalPeriod",
 *     required={"id", "name", "days", "is_active"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Monthly"),
 *     @OA\Property(property="days", type="integer", example=30),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProductPricing",
 *     required={"id", "region", "rental_period", "price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(
 *         property="region",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="North America"),
 *         @OA\Property(property="code", type="string", example="NA")
 *     ),
 *     @OA\Property(
 *         property="rental_period",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Monthly"),
 *         @OA\Property(property="days", type="integer", example=30)
 *     ),
 *     @OA\Property(property="price", type="number", format="float", example=49.99)
 * )
 */
