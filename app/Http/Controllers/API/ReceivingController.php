<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceiveItemsRequest;
use App\Http\Requests\RejectItemsRequest;
use App\Http\Requests\CloseReceivingRequest;
use App\Http\Requests\QualityInspectionRequest;
use App\Models\PurchaseOrder;
use App\Services\ReceivingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceivingController extends Controller
{
    /**
     * @var ReceivingService
     */
    protected $receivingService;

    /**
     * Constructor injects the ReceivingService dependency
     *
     * @param ReceivingService $receivingService Service for handling receiving operations
     */
    public function __construct(ReceivingService $receivingService)
    {
        $this->receivingService = $receivingService;
    }

    /**
     * Get a list of purchase orders available for receiving
     * 
     * @param Request $request HTTP request
     * @return JsonResponse JSON response with purchase orders data
     */
    public function getReceivablePurchaseOrders(Request $request): JsonResponse
    {
        // Get query parameters for filtering
        $status = $request->query('status', ['sent', 'confirmed', 'partially_received']);
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $search = $request->query('search');
        
        // Start building the query
        $query = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereIn('status', is_array($status) ? $status : [$status]);
        
        // Apply filters if provided
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        if ($fromDate) {
            $query->where('order_date', '>=', $fromDate);
        }
        
        if ($toDate) {
            $query->where('order_date', '<=', $toDate);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Paginate the results
        $perPage = $request->query('per_page', 15);
        $purchaseOrders = $query->paginate($perPage);
        
        // Return the results as JSON
        return response()->json($purchaseOrders);
    }

    /**
     * Get detailed information about a specific purchase order for receiving
     * 
     * @param int $id Purchase order ID
     * @return JsonResponse JSON response with purchase order details
     */
    public function getPurchaseOrderForReceiving(int $id): JsonResponse
    {
        // Load the purchase order with needed relationships
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'warehouse',
            'items.product',
            'items.destinationLocation',
            'receiptTransactions',
        ])->findOrFail($id);
        
        // Return the purchase order data
        return response()->json($purchaseOrder);
    }

    /**
     * Process a receipt transaction for a purchase order
     * 
     * @param ReceiveItemsRequest $request Validated request for receiving items
     * @param int $id Purchase order ID
     * @return JsonResponse JSON response with receipt results
     */
    public function receiveItems(ReceiveItemsRequest $request, int $id): JsonResponse
    {
        try {
            // Process the receipt using the receiving service
            $receiptResults = $this->receivingService->receiveItems(
                $id,
                $request->input('items'),
                $request->input('metadata', [])
            );
            
            // Return the receipt results as JSON
            return response()->json([
                'success' => true,
                'message' => 'Items received successfully',
                'data' => $receiptResults,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error processing receipt: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process rejected items for a purchase order
     * 
     * @param RejectItemsRequest $request Validated request for rejecting items
     * @param int $id Purchase order ID
     * @return JsonResponse JSON response with rejection results
     */
    public function rejectItems(RejectItemsRequest $request, int $id): JsonResponse
    {
        try {
            // Process the rejections using the receiving service
            $rejectionResults = $this->receivingService->rejectItems(
                $id,
                $request->input('items'),
                $request->input('reason')
            );
            
            // Return the rejection results as JSON
            return response()->json([
                'success' => true,
                'message' => 'Items rejected successfully',
                'data' => $rejectionResults,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error processing rejection: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Close a purchase order after receiving
     * 
     * @param CloseReceivingRequest $request Validated request for closing
     * @param int $id Purchase order ID
     * @return JsonResponse JSON response indicating success or failure
     */
    public function closePurchaseOrder(CloseReceivingRequest $request, int $id): JsonResponse
    {
        try {
            // Close the purchase order using the receiving service
            $success = $this->receivingService->closePurchaseOrder(
                $id,
                $request->input('notes')
            );
            
            // Return a success or failure response
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase order closed successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to close purchase order',
                ], 400);
            }
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error closing purchase order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process quality inspection results for received items
     * 
     * @param QualityInspectionRequest $request Validated request for quality inspection
     * @param int $id Purchase order ID
     * @return JsonResponse JSON response with inspection results
     */
    public function processQualityInspection(QualityInspectionRequest $request, int $id): JsonResponse
    {
        try {
            // Process the quality inspection using the receiving service
            $inspectionResults = $this->receivingService->processQualityInspection(
                $id,
                $request->input('items')
            );
            
            // Return the inspection results as JSON
            return response()->json([
                'success' => true,
                'message' => 'Quality inspection processed successfully',
                'data' => $inspectionResults,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error processing quality inspection: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate a receiving report for a purchase order
     * 
     * @param int $id Purchase order ID
     * @return JsonResponse JSON response with report data
     */
    public function generateReceivingReport(int $id): JsonResponse
    {
        try {
            // Generate the report using the receiving service
            $reportData = $this->receivingService->generateReceivingReport($id);
            
            // Return the report data as JSON
            return response()->json([
                'success' => true,
                'data' => $reportData,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage(),
            ], 400);
        }
    }
}