<?php

namespace App\Services;

use App\Models\BinLocation;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceivingService
{
    /**
     * @var FifoInventoryStrategy Inventory strategy for FIFO implementation
     */
    protected $inventoryStrategy;

    /**
     * Constructor to inject dependencies
     * 
     * @param FifoInventoryStrategy $inventoryStrategy Strategy for inventory management
     */
    public function __construct(FifoInventoryStrategy $inventoryStrategy)
    {
        // Inject the FIFO inventory strategy to handle inventory updates
        $this->inventoryStrategy = $inventoryStrategy;
    }

    /**
     * Process a purchase order receipt
     * This is the main method for receiving goods against a purchase order
     * 
     * @param int $purchaseOrderId Purchase order ID
     * @param array $receiptData Array of items being received
     * @param array $metadata Additional metadata about the receipt
     * @return array Information about the processed receipt
     */
    public function receiveItems(int $purchaseOrderId, array $receiptData, array $metadata = []): array
    {
        // Find the purchase order
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        
        // Validate that the purchase order is in a receivable state
        if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partially_received'])) {
            throw new \Exception('Purchase order is not in a receivable state.');
        }
        
        // Begin a database transaction to ensure all operations succeed or fail together
        return DB::transaction(function () use ($purchaseOrder, $receiptData, $metadata) {
            $receivedItems = [];
            $movementIds = [];
            
            // Get the currently authenticated user
            $userId = Auth::id();
            
            // Process each item in the receipt data
            foreach ($receiptData as $itemData) {
                // Extract and validate item data
                $poItemId = $itemData['purchase_order_item_id'];
                $quantity = (float) $itemData['quantity'];
                $locationId = $itemData['location_id'] ?? null;
                $lotNumber = $itemData['lot_number'] ?? null;
                $notes = $itemData['notes'] ?? null;
                
                // Skip items with zero quantity
                if ($quantity <= 0) {
                    continue;
                }
                
                // Find the purchase order item
                $poItem = PurchaseOrderItem::findOrFail($poItemId);
                
                // Ensure the item belongs to the purchase order
                if ($poItem->purchase_order_id !== $purchaseOrder->id) {
                    throw new \Exception('Item does not belong to the specified purchase order.');
                }
                
                // If no location is specified, use the item's destination location
                if (!$locationId && $poItem->destination_location_id) {
                    $locationId = $poItem->destination_location_id;
                }
                
                // Ensure we have a valid location
                if (!$locationId) {
                    throw new \Exception('No valid location specified for receiving item.');
                }
                
                // Find the location
                $location = BinLocation::findOrFail($locationId);
                
                // Create an inventory movement for this receipt
                $movement = new InventoryMovement([
                    'reference_type' => get_class($purchaseOrder),
                    'reference_id' => $purchaseOrder->id,
                    'product_id' => $poItem->product_id,
                    'to_location_id' => $locationId,
                    'quantity' => $quantity,
                    'unit_of_measure' => $poItem->unit_of_measure,
                    'lot_number' => $lotNumber,
                    'movement_type' => 'receive',
                    'reason' => 'Purchase order receipt',
                    'performed_by' => $userId,
                ]);
                
                $movement->save();
                $movementIds[] = $movement->id;
                
                // Update inventory using the FIFO inventory strategy
                $this->inventoryStrategy->receive(
                    $poItem->product_id,
                    $locationId,
                    $quantity,
                    $poItem->unit_price,
                    [
                        'lot_number' => $lotNumber,
                        'purchase_order_id' => $purchaseOrder->id,
                        'purchase_order_item_id' => $poItem->id,
                        'movement_id' => $movement->id,
                    ]
                );
                
                // Record the receipt on the purchase order item
                $poItem->recordReceipt($quantity, $lotNumber, $userId, $notes);
                
                // Add to the list of received items
                $receivedItems[] = [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'product_name' => $poItem->product->name,
                    'quantity' => $quantity,
                    'location_id' => $locationId,
                    'location_name' => $location->name,
                    'lot_number' => $lotNumber,
                ];
            }
            
            // Update supplier performance metrics
            $purchaseOrder->supplier->updatePerformanceMetrics($purchaseOrder);
            
            // Return information about the processed receipt
            return [
                'purchase_order' => $purchaseOrder->refresh(),
                'received_items' => $receivedItems,
                'movement_ids' => $movementIds,
                'receipt_date' => Carbon::now()->toDateTimeString(),
                'received_by' => $userId,
                'metadata' => $metadata,
            ];
        });
    }

    /**
     * Process rejected items in a purchase order receipt
     * This handles items that failed quality inspection
     * 
     * @param int $purchaseOrderId Purchase order ID
     * @param array $rejectionData Array of items being rejected
     * @param string $reason Reason for rejection
     * @return array Information about the processed rejections
     */
    public function rejectItems(int $purchaseOrderId, array $rejectionData, string $reason): array
    {
        // Find the purchase order
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        
        // Begin a database transaction
        return DB::transaction(function () use ($purchaseOrder, $rejectionData, $reason) {
            $rejectedItems = [];
            
            // Get the currently authenticated user
            $userId = Auth::id();
            
            // Process each item in the rejection data
            foreach ($rejectionData as $itemData) {
                // Extract and validate item data
                $poItemId = $itemData['purchase_order_item_id'];
                $quantity = (float) $itemData['quantity'];
                
                // Skip items with zero quantity
                if ($quantity <= 0) {
                    continue;
                }
                
                // Find the purchase order item
                $poItem = PurchaseOrderItem::findOrFail($poItemId);
                
                // Ensure the item belongs to the purchase order
                if ($poItem->purchase_order_id !== $purchaseOrder->id) {
                    throw new \Exception('Item does not belong to the specified purchase order.');
                }
                
                // Record the rejection on the purchase order item
                $poItem->recordRejection($quantity, $reason, $userId);
                
                // Add to the list of rejected items
                $rejectedItems[] = [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'product_name' => $poItem->product->name,
                    'quantity' => $quantity,
                ];
            }
            
            // Return information about the processed rejections
            return [
                'purchase_order' => $purchaseOrder->refresh(),
                'rejected_items' => $rejectedItems,
                'rejection_date' => Carbon::now()->toDateTimeString(),
                'rejected_by' => $userId,
                'reason' => $reason,
            ];
        });
    }

    /**
     * Close a purchase order after receiving
     * This marks the purchase order as closed and handles any final processing
     * 
     * @param int $purchaseOrderId Purchase order ID
     * @param string|null $notes Additional notes about closing
     * @return bool Whether the operation was successful
     */
    public function closePurchaseOrder(int $purchaseOrderId, ?string $notes = null): bool
    {
        // Find the purchase order
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        
        // Validate that the purchase order can be closed
        if (!in_array($purchaseOrder->status, ['partially_received', 'fully_received'])) {
            throw new \Exception('Purchase order must be partially or fully received before closing.');
        }
        
        // Update the purchase order status
        $purchaseOrder->status = 'closed';
        
        // Add closing notes if provided
        if ($notes) {
            $purchaseOrder->notes = $purchaseOrder->notes 
                ? $purchaseOrder->notes . "\n\nClosing notes: " . $notes
                : "Closing notes: " . $notes;
        }
        
        // Get the currently authenticated user
        $userId = Auth::id();
        
        // Log the closure event in the purchase order history
        // Note: You might want to add a purchase_order_history table to track events
        
        return $purchaseOrder->save();
    }

    /**
     * Generate a receiving report for a purchase order
     * This creates a summary of all receiving transactions
     * 
     * @param int $purchaseOrderId Purchase order ID
     * @return array Report data
     */
    public function generateReceivingReport(int $purchaseOrderId): array
    {
        // Find the purchase order with related data
        $purchaseOrder = PurchaseOrder::with([
            'supplier', 
            'warehouse', 
            'items.product', 
            'receiptTransactions'
        ])->findOrFail($purchaseOrderId);
        
        // Prepare the report data
        $reportData = [
            'purchase_order' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'order_date' => $purchaseOrder->order_date->format('Y-m-d'),
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date 
                    ? $purchaseOrder->expected_delivery_date->format('Y-m-d') 
                    : null,
                'status' => $purchaseOrder->status,
                'total_amount' => $purchaseOrder->total_amount,
            ],
            'supplier' => [
                'id' => $purchaseOrder->supplier->id,
                'name' => $purchaseOrder->supplier->name,
                'code' => $purchaseOrder->supplier->code,
            ],
            'warehouse' => [
                'id' => $purchaseOrder->warehouse->id,
                'name' => $purchaseOrder->warehouse->name,
                'code' => $purchaseOrder->warehouse->code,
            ],
            'items' => [],
            'receipt_transactions' => [],
            'summary' => [
                'total_items_ordered' => 0,
                'total_items_received' => 0,
                'total_items_rejected' => 0,
                'receipt_completion_percentage' => $purchaseOrder->received_percentage,
            ],
        ];
        
        // Add item data
        foreach ($purchaseOrder->items as $item) {
            $reportData['items'][] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'quantity_ordered' => $item->quantity_ordered,
                'quantity_received' => $item->quantity_received,
                'quantity_rejected' => $item->quantity_rejected,
                'remaining_quantity' => $item->remaining_quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'status' => $item->status,
                'receipt_completion_percentage' => $item->receipt_completion_percentage,
            ];
            
            // Update summary totals
            $reportData['summary']['total_items_ordered'] += $item->quantity_ordered;
            $reportData['summary']['total_items_received'] += $item->quantity_received;
            $reportData['summary']['total_items_rejected'] += $item->quantity_rejected;
        }
        
        // Add receipt transaction data
        foreach ($purchaseOrder->receiptTransactions as $transaction) {
            $reportData['receipt_transactions'][] = [
                'id' => $transaction->id,
                'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                'product_id' => $transaction->product_id,
                'product_name' => $transaction->product->name,
                'location_id' => $transaction->to_location_id,
                'location_name' => $transaction->toLocation->name,
                'quantity' => $transaction->quantity,
                'lot_number' => $transaction->lot_number,
                'performed_by' => $transaction->performer ? $transaction->performer->name : null,
            ];
        }
        
        return $reportData;
    }

    /**
     * Handle a quality inspection for received items
     * This can update the status of items based on inspection results
     * 
     * @param int $purchaseOrderId Purchase order ID
     * @param array $inspectionData Array of inspection results
     * @return array Information about the inspection results
     */
    public function processQualityInspection(int $purchaseOrderId, array $inspectionData): array
    {
        // Find the purchase order
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        
        // Begin a database transaction
        return DB::transaction(function () use ($purchaseOrder, $inspectionData) {
            $inspectionResults = [];
            
            // Get the currently authenticated user
            $userId = Auth::id();
            
            // Process each item in the inspection data
            foreach ($inspectionData as $itemData) {
                // Extract and validate item data
                $poItemId = $itemData['purchase_order_item_id'];
                $inspectionStatus = $itemData['status']; // 'pass' or 'fail'
                $notes = $itemData['notes'] ?? null;
                $quantityRejected = $itemData['quantity_rejected'] ?? 0;
                $rejectionReason = $itemData['rejection_reason'] ?? null;
                
                // Find the purchase order item
                $poItem = PurchaseOrderItem::findOrFail($poItemId);
                
                // Ensure the item belongs to the purchase order
                if ($poItem->purchase_order_id !== $purchaseOrder->id) {
                    throw new \Exception('Item does not belong to the specified purchase order.');
                }
                
                // Process based on inspection status
                if ($inspectionStatus === 'pass') {
                    // No action needed for passing items
                    $result = 'pass';
                } else if ($inspectionStatus === 'fail' && $quantityRejected > 0) {
                    // Record rejection for failing items
                    if (!$rejectionReason) {
                        throw new \Exception('Rejection reason is required for failed inspections.');
                    }
                    
                    // Ensure rejection quantity doesn't exceed received quantity
                    if ($quantityRejected > $poItem->quantity_received) {
                        throw new \Exception('Rejection quantity cannot exceed received quantity.');
                    }
                    
                    // Record the rejection
                    $poItem->recordRejection($quantityRejected, $rejectionReason, $userId);
                    
                    $result = 'fail';
                } else {
                    throw new \Exception('Invalid inspection status or missing required data.');
                }
                
                // Add to inspection results
                $inspectionResults[] = [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'product_name' => $poItem->product->name,
                    'inspection_status' => $inspectionStatus,
                    'quantity_rejected' => $quantityRejected,
                    'rejection_reason' => $rejectionReason,
                    'notes' => $notes,
                    'result' => $result,
                ];
            }
            
            // Return information about the inspection
            return [
                'purchase_order' => $purchaseOrder->refresh(),
                'inspection_results' => $inspectionResults,
                'inspection_date' => Carbon::now()->toDateTimeString(),
                'inspected_by' => $userId,
            ];
        });
    }
}