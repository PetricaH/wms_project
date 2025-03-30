<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordPickRequest;
use App\Http\Requests\BatchPickRequest;
use App\Http\Requests\CompletePickingRequest;
use App\Http\Requests\WavePickRequest;
use App\Services\PickingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PickingController extends Controller
{
    /**
     * @var PickingService Service for handling picking operations
     */
    protected $pickingService;

    /**
     * Constructor injects the PickingService dependency
     *
     * @param PickingService $pickingService Service for picking operations
     */
    public function __construct(PickingService $pickingService)
    {
        $this->pickingService = $pickingService;
    }

    /**
     * Record a single item pick
     * 
     * @param RecordPickRequest $request Validated request for recording a pick
     * @return JsonResponse JSON response with pick result
     */
    public function recordPick(RecordPickRequest $request): JsonResponse
    {
        try {
            // Process the pick using the picking service
            $pickResult = $this->pickingService->recordPick(
                $request->input('order_item_id'),
                $request->input('quantity'),
                $request->input('location_id'),
                $request->input('additional_data', [])
            );
            
            // Return a success response with the pick result
            return response()->json([
                'success' => true,
                'message' => 'Pick recorded successfully',
                'data' => $pickResult,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error recording pick: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Record multiple picks for an order in a batch
     * 
     * @param BatchPickRequest $request Validated request for batch picking
     * @return JsonResponse JSON response with batch pick results
     */
    public function recordBatchPicks(BatchPickRequest $request): JsonResponse
    {
        try {
            // Process the batch picks using the picking service
            $batchResults = $this->pickingService->recordBatchPicks(
                $request->input('order_id'),
                $request->input('picks')
            );
            
            // Return a success response with the batch results
            return response()->json([
                'success' => true,
                'message' => 'Batch picks recorded successfully',
                'data' => $batchResults,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error recording batch picks: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete the picking process for an order
     * 
     * @param CompletePickingRequest $request Validated request for completing picking
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with completion result
     */
    public function completeOrderPicking(CompletePickingRequest $request, int $orderId): JsonResponse
    {
        try {
            // Complete the picking process using the picking service
            $completionResult = $this->pickingService->completeOrderPicking(
                $orderId,
                $request->input('completion_data', [])
            );
            
            // Return a success response with the completion result
            return response()->json([
                'success' => true,
                'message' => 'Order picking completed successfully',
                'data' => $completionResult,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error completing picking: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate a wave pick list for multiple orders
     * 
     * @param WavePickRequest $request Validated request for wave picking
     * @return JsonResponse JSON response with wave pick list
     */
    public function generateWavePickList(WavePickRequest $request): JsonResponse
    {
        try {
            // Generate the wave pick list using the picking service
            $wavePickList = $this->pickingService->generateWavePickList(
                $request->input('order_ids'),
                $request->input('warehouse_id')
            );
            
            // Return a success response with the wave pick list
            return response()->json([
                'success' => true,
                'message' => 'Wave pick list generated successfully',
                'data' => $wavePickList,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error generating wave pick list: ' . $e->getMessage(),
            ], 400);
        }
    }
}