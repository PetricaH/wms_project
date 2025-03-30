<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\Api\BinLocationController;
use App\Http\Controllers\Api\InventoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes that require authentication
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    
    // Other API routes that require authentication would go here
    // They should also typically include the tenant middleware
    Route::middleware('tenant')->group(function () {
        // Future tenant-specific API endpoints
        // e.g., products, inventory, locations, etc.
    });
});

// API routes that require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // Product management
    Route::apiResource('products', ProductController::class);
    Route::get('products/{product}/inventory', [ProductController::class, 'inventory']);
    
    // Category management
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/tree', [CategoryController::class, 'tree']);
    
    // Warehouse management
    Route::apiResource('warehouses', WarehouseController::class);
    Route::get('warehouses/{warehouse}/zones', [WarehouseController::class, 'zones']);
    Route::get('warehouses/{warehouse}/bin-locations', [WarehouseController::class, 'binLocations']);
    
    // Zone management
    Route::apiResource('zones', ZoneController::class);
    Route::get('zones/{zone}/bin-locations', [ZoneController::class, 'binLocations']);
    
    // Bin location management
    Route::apiResource('bin-locations', BinLocationController::class);
    
    // Inventory management
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/summary', [InventoryController::class, 'summary']);
        Route::get('/movements', [InventoryController::class, 'movements']);
        
        // Inventory operations
        Route::post('/receive', [InventoryController::class, 'receive']);
        Route::post('/transfer', [InventoryController::class, 'transfer']);
        Route::post('/pick', [InventoryController::class, 'pick']);
        Route::post('/adjust', [InventoryController::class, 'adjust']);
    });
});