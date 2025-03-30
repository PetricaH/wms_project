<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackOrderItemRequest;
use App\Http\Requests\PackOrderItemsRequest;
use App\Http\Requests\CompletePackingRequest;
use App\Services\PackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackingController extends Controller
{
    /**
     * @var PackingService Service for handling packing operations
     */
    protected $packingService;

    /**
     * Constructor injects the PackingService dependency
     *
     * @param PackingService $packingService Service for packing operations
     */
    public function __construct(PackingService $packingService)
    {
        $this->packingService = $packingService;
    }

    /**
     * Pack a single order item
     * 
     * @param PackOrderItemRequest $request Validated request for packing an item
     * @return JsonResponse JSON response with packing result
     */
    public function packOrderItem(PackOrderItemRequest $request): JsonResponse
    {
        try {
            // Process the packing using the packing service
            $packingResult = $this->packingService->packOrderItem(
                $request->input('order_item_id'),
                $request->input('packing_data')
            );
            
            // Return a success response with the packing result
            return response()->json([
                'success' => true,
                'message' => 'Item packed successfully',
                'data' => $packingResult,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error packing item: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Pack multiple items for an order
     * 
     * @param PackOrderItemsRequest $request Validated request for packing multiple items
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with packing results
     */
    public function packOrderItems(PackOrderItemsRequest $request, int $orderId): JsonResponse
    {
        try {
            // Process the batch packing using the packing service
            $packingResults = $this->packingService->packOrderItems(
                $orderId,
                $request->input('packing_data')
            );
            
            // Return a success response with the packing results
            return response()->json([
                'success' => true,
                'message' => 'Items packed successfully',
                'data' => $packingResults,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error packing items: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete the packing process for an order
     * 
     * @param CompletePackingRequest $request Validated request for completing packing
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with completion result
     */
    public function completeOrderPacking(CompletePackingRequest $request, int $orderId): JsonResponse
    {
        try {
            // Complete the packing process using the packing service
            $completionResult = $this->packingService->completeOrderPacking(
                $orderId,
                $request->input('completion_data', [])
            );
            
            // Return a success response with the completion result
            return response()->json([
                'success' => true,
                'message' => 'Order packing completed successfully',
                'data' => $completionResult,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error completing packing: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate packing materials recommendation for an order
     * 
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with packing recommendations
     */
    public function generatePackingRecommendation(int $orderId): JsonResponse
    {
        try {
            // Generate the recommendations using the packing service
            $recommendations = $this->packingService->generatePackingRecommendation($orderId);
            
            // Return a success response with the recommendations
            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error generating packing recommendations: ' . $e->getMessage(),
            ], 400);
        }
    }
}