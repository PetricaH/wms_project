<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShipOrderRequest;
use App\Http\Requests\ManualShipRequest;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * @var ShippingService Service for handling shipping operations
     */
    protected $shippingService;

    /**
     * Constructor injects the ShippingService dependency
     *
     * @param ShippingService $shippingService Service for shipping operations
     */
    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Get available shipping rates for an order
     * 
     * @param int $orderId Order ID
     * @param Request $request HTTP request with optional provider parameter
     * @return JsonResponse JSON response with shipping rates
     */
    public function getShippingRates(int $orderId, Request $request): JsonResponse
    {
        try {
            // Set a specific provider if requested
            if ($request->has('provider')) {
                $this->shippingService->setProvider($request->input('provider'));
            }
            
            // Get shipping rates using the shipping service
            $rateResponse = $this->shippingService->getOrderShippingRates($orderId);
            
            // Return a success response with the rates
            return response()->json([
                'success' => true,
                'data' => $rateResponse,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error getting shipping rates: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Ship an order using a shipping provider
     * 
     * @param ShipOrderRequest $request Validated request for shipping an order
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with shipment details
     */
    public function shipOrder(ShipOrderRequest $request, int $orderId): JsonResponse
    {
        try {
            // Ship the order using the shipping service
            $shipmentResponse = $this->shippingService->shipOrder(
                $orderId,
                $request->input('shipping_data')
            );
            
            // Return a success response with the shipment details
            return response()->json([
                'success' => true,
                'message' => 'Order shipped successfully',
                'data' => $shipmentResponse,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error shipping order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark an order as shipped manually without using a shipping provider
     * 
     * @param ManualShipRequest $request Validated request for manual shipping
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with shipment details
     */
    public function markOrderShipped(ManualShipRequest $request, int $orderId): JsonResponse
    {
        try {
            // Mark the order as shipped using the shipping service
            $shipmentResponse = $this->shippingService->markOrderShipped(
                $orderId,
                $request->input('shipping_data')
            );
            
            // Return a success response with the shipment details
            return response()->json([
                'success' => true,
                'message' => 'Order marked as shipped successfully',
                'data' => $shipmentResponse,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error marking order as shipped: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Track an order shipment
     * 
     * @param int $orderId Order ID
     * @return JsonResponse JSON response with tracking details
     */
    public function trackOrderShipment(int $orderId): JsonResponse
    {
        try {
            // Track the shipment using the shipping service
            $trackingResponse = $this->shippingService->trackOrderShipment($orderId);
            
            // Return a success response with the tracking details
            return response()->json([
                'success' => true,
                'data' => $trackingResponse,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error tracking shipment: ' . $e->getMessage(),
            ], 400);
        }
    }
}