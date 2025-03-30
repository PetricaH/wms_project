<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers from Phase 1 & 2
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\Api\BinLocationController;
use App\Http\Controllers\Api\InventoryController;

// New Controllers from Phase 3
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReceivingController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PickingController;
use App\Http\Controllers\Api\PackingController;
use App\Http\Controllers\Api\ShippingController;

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
});

// API routes that require authentication
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // PHASE 2: Product & Inventory Management
    
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
    
    // PHASE 3: Receiving and Order Processing
    
    // Supplier Management
    Route::apiResource('suppliers', SupplierController::class);
    
    // Purchase Order Management
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('purchase-orders/{id}/send', [PurchaseOrderController::class, 'send']);
    Route::post('purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);
    
    // Receiving
    Route::prefix('receiving')->group(function () {
        Route::get('/purchase-orders', [ReceivingController::class, 'getReceivablePurchaseOrders']);
        Route::get('/purchase-orders/{id}', [ReceivingController::class, 'getPurchaseOrderForReceiving']);
        Route::post('/purchase-orders/{id}/receive', [ReceivingController::class, 'receiveItems']);
        Route::post('/purchase-orders/{id}/reject', [ReceivingController::class, 'rejectItems']);
        Route::post('/purchase-orders/{id}/close', [ReceivingController::class, 'closePurchaseOrder']);
        Route::post('/purchase-orders/{id}/quality-inspection', [ReceivingController::class, 'processQualityInspection']);
        Route::get('/purchase-orders/{id}/report', [ReceivingController::class, 'generateReceivingReport']);
    });
    
    // Order Management
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{id}/payment', [OrderController::class, 'processPayment']);
    Route::post('orders/{id}/allocate', [OrderController::class, 'allocateInventory']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{id}/assign', [OrderController::class, 'assignToUser']);
    Route::get('orders/ready-for-picking', [OrderController::class, 'getOrdersReadyForPicking']);
    Route::get('orders/{id}/pick-list', [OrderController::class, 'generatePickList']);
    
    // Picking
    Route::prefix('picking')->group(function () {
        Route::post('/record', [PickingController::class, 'recordPick']);
        Route::post('/batch', [PickingController::class, 'recordBatchPicks']);
        Route::post('/orders/{orderId}/complete', [PickingController::class, 'completeOrderPicking']);
        Route::post('/wave', [PickingController::class, 'generateWavePickList']);
    });
    
    // Packing
    Route::prefix('packing')->group(function () {
        Route::post('/item', [PackingController::class, 'packOrderItem']);
        Route::post('/orders/{orderId}/items', [PackingController::class, 'packOrderItems']);
        Route::post('/orders/{orderId}/complete', [PackingController::class, 'completeOrderPacking']);
        Route::get('/orders/{orderId}/recommendations', [PackingController::class, 'generatePackingRecommendation']);
    });
    
    // Shipping
    Route::prefix('shipping')->group(function () {
        Route::get('/orders/{orderId}/rates', [ShippingController::class, 'getShippingRates']);
        Route::post('/orders/{orderId}/ship', [ShippingController::class, 'shipOrder']);
        Route::post('/orders/{orderId}/manual-ship', [ShippingController::class, 'markOrderShipped']);
        Route::get('/orders/{orderId}/track', [ShippingController::class, 'trackOrderShipment']);
    });
});