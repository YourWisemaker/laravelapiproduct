<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\RentalPeriodController;
use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\AttributeValueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes with versioning
Route::prefix('v1')->group(function () {
    // Product Routes - Complete CRUD with filtering and pagination
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::get('/products/{product}/pricing', [ProductController::class, 'getPricing']);
    
    // Product Pricing Routes
    Route::post('/pricing', [PricingController::class, 'store']);
    Route::put('/pricing/{pricing}', [PricingController::class, 'update']);
    Route::delete('/pricing/{pricing}', [PricingController::class, 'destroy']);
    
    // Rental Routes
    Route::get('/rentals', [RentalController::class, 'index']);
    Route::post('/rentals', [RentalController::class, 'store']);
    Route::get('/rentals/{rental}', [RentalController::class, 'show']);
    Route::put('/rentals/{rental}', [RentalController::class, 'update']);
    
    // Reference Data Routes
    Route::get('/regions', [RegionController::class, 'index']);
    Route::post('/regions', [RegionController::class, 'store']);
    Route::put('/regions/{region}', [RegionController::class, 'update']);
    Route::delete('/regions/{region}', [RegionController::class, 'destroy']);
    
    // Rental Period Routes
    Route::get('/rental-periods', [RentalPeriodController::class, 'index']);
    Route::post('/rental-periods', [RentalPeriodController::class, 'store']);
    Route::get('/rental-periods/{rentalPeriod}', [RentalPeriodController::class, 'show']);
    Route::put('/rental-periods/{rentalPeriod}', [RentalPeriodController::class, 'update']);
    Route::delete('/rental-periods/{rentalPeriod}', [RentalPeriodController::class, 'destroy']);
    
    // Attribute Routes
    Route::get('/attributes', [AttributeController::class, 'index']);
    Route::post('/attributes', [AttributeController::class, 'store']);
    Route::put('/attributes/{attribute}', [AttributeController::class, 'update']);
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy']);
    
    // Attribute Value Routes
    Route::get('/attribute-values', [AttributeValueController::class, 'index']);
    Route::post('/attribute-values', [AttributeValueController::class, 'store']);
    Route::put('/attribute-values/{attributeValue}', [AttributeValueController::class, 'update']);
    Route::delete('/attribute-values/{attributeValue}', [AttributeValueController::class, 'destroy']);
});