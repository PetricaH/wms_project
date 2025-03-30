<?php

namespace App\Services;

use App\Models\BinLocation;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PickingService
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
     * Record a pick operation for an order item
     * This confirms that items have been picked from inventory
     * 
     * @param int $orderItemId Order item ID
     * @param float $quantity Quantity picked
     * @param int $locationId Bin location ID where the item was picked from
     * @param array $additionalData Additional data about the pick (lot number, notes, etc.)
     * @return array Information about the processed pick
     */
    public function recordPick(int $orderItemId, float $quantity, int $locationId, array $additionalData = []): array
    {
        // Find the order item
        $orderItem = OrderItem::with('order', 'product')->findOrFail($orderItemId);
        
        // Validate that the order item is in a pickable state
        if (!in_array($orderItem->status, ['pending', 'allocated', 'partially_allocated', 'picking'])) {
            throw new \Exception("Order item with status '{$orderItem->status}' cannot be picked.");
        }
        
        // Check that we're not picking more than the remaining quantity
        $remainingToPick = $orderItem->quantity - $orderItem->quantity_picked;
        if ($quantity > $remainingToPick) {
            throw new \Exception("Cannot pick more than the remaining quantity ({$remainingToPick}).");
        }
        
        // Find the location
        $location = BinLocation::findOrFail($locationId);
        
        // Begin a database transaction to ensure all operations succeed or fail together
        return DB::transaction(function () use ($orderItem, $quantity, $locationId, $additionalData) {
            // Get additional data
            $lotNumber = $additionalData['lot_number'] ?? null;
            $notes = $additionalData['notes'] ?? null;
            $userId = Auth::id();
            
            // Create an inventory movement for this pick
            $movement = new InventoryMovement([
                'reference_type' => get_class($orderItem->order),
                'reference_id' => $orderItem->order->id,
                'product_id' => $orderItem->product_id,
                'from_location_id' => $locationId,
                'quantity' => $quantity,
                'unit_of_measure' => $orderItem->unit_of_measure,
                'lot_number' => $lotNumber,
                'movement_type' => 'pick',
                'reason' => 'Order fulfillment',
                'performed_by' => $userId,
            ]);
            
            $movement->save();
            
            // Update inventory using the FIFO inventory strategy
            $this->inventoryStrategy->pick(
                $orderItem->product_id,
                $locationId,
                $quantity,
                [
                    'lot_number' => $lotNumber,
                    'order_id' => $orderItem->order->id,
                    'order_item_id' => $orderItem->id,
                    'movement_id' => $movement->id,
                ]
            );
            
            // Record the pick on the order item
            $orderItem->recordPicking($quantity, $locationId, $lotNumber, $userId, $notes);
            
            // Return information about the processed pick
            return [
                'order_item' => $orderItem->refresh(),
                'order' => $orderItem->order->refresh(),
                'movement_id' => $movement->id,
                'quantity' => $quantity,
                'location_id' => $locationId,
                'lot_number' => $lotNumber,
                'picked_by' => $userId,
                'picked_at' => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Record multiple picks for an order in a single transaction
     * This is used when completing a pick list
     * 
     * @param int $orderId Order ID
     * @param array $pickData Array of pick operations
     * @return array Information about the processed picks
     */
    public function recordBatchPicks(int $orderId, array $pickData): array
    {
        // Find the order
        $order = Order::with('items')->findOrFail($orderId);
        
        // Validate that the order is in a pickable state
        if (!in_array($order->status, ['ready_to_pick', 'picking'])) {
            throw new \Exception("Order with status '{$order->status}' cannot be picked.");
        }
        
        // Begin a database transaction to ensure all operations succeed or fail together
        return DB::transaction(function () use ($order, $pickData) {
            $processedPicks = [];
            
            // Process each pick operation
            foreach ($pickData as $pick) {
                // Validate required fields
                if (!isset($pick['order_item_id']) || !isset($pick['quantity']) || !isset($pick['location_id'])) {
                    throw new \Exception("Missing required fields for pick operation.");
                }
                
                // Find the order item
                $orderItemId = $pick['order_item_id'];
                $quantity = (float) $pick['quantity'];
                $locationId = $pick['location_id'];
                $additionalData = [
                    'lot_number' => $pick['lot_number'] ?? null,
                    'notes' => $pick['notes'] ?? null,
                ];
                
                // Record the individual pick
                $pickResult = $this->recordPick($orderItemId, $quantity, $locationId, $additionalData);
                $processedPicks[] = $pickResult;
            }
            
            // Check if all items have been fully picked
            $fullyPicked = true;
            foreach ($order->items as $item) {
                // Refresh the item to get the latest data
                $item->refresh();
                
                // Skip cancelled items
                if ($item->status === 'cancelled') {
                    continue;
                }
                
                if ($item->quantity_picked < $item->quantity) {
                    $fullyPicked = false;
                    break;
                }
            }
            
            // If all items are fully picked, transition the order to picked status
            if ($fullyPicked) {
                $order->transitionTo('picked', Auth::id());
            }
            
            // Return information about the processed picks
            return [
                'order' => $order->refresh(),
                'picks' => $processedPicks,
                'fully_picked' => $fullyPicked,
                'processed_at' => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Complete the picking process for an order
     * This finalizes the pick and transitions the order to the picked state
     * 
     * @param int $orderId Order ID
     * @param array $completionData Additional data about the pick completion
     * @return array Information about the completed pick
     */
    public function completeOrderPicking(int $orderId, array $completionData = []): array
    {
        // Find the order
        $order = Order::with('items')->findOrFail($orderId);
        
        // Validate that the order is in a pickable state
        if (!in_array($order->status, ['picking'])) {
            throw new \Exception("Order with status '{$order->status}' cannot complete picking.");
        }
        
        // Begin a database transaction
        return DB::transaction(function () use ($order, $completionData) {
            $userId = Auth::id();
            $notes = $completionData['notes'] ?? null;
            
            // Check if all items have been fully picked
            $fullyPicked = true;
            $pickSummary = [];
            
            foreach ($order->items as $item) {
                // Skip cancelled items
                if ($item->status === 'cancelled') {
                    continue;
                }
                
                // Check if this item is fully picked
                $itemFullyPicked = $item->quantity_picked >= $item->quantity;
                
                if (!$itemFullyPicked) {
                    $fullyPicked = false;
                }
                
                // Record item pick status in summary
                $pickSummary[] = [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->name,
                    'product_sku' => $item->sku,
                    'quantity_ordered' => $item->quantity,
                    'quantity_picked' => $item->quantity_picked,
                    'fully_picked' => $itemFullyPicked,
                ];
            }
            
            // Transition the order to picked status
            $order->transitionTo('picked', $userId);
            
            // Return information about the completed pick
            return [
                'order' => $order->refresh(),
                'fully_picked' => $fullyPicked,
                'pick_summary' => $pickSummary,
                'completed_by' => $userId,
                'completed_at' => now()->toDateTimeString(),
                'notes' => $notes,
            ];
        });
    }

    /**
     * Generate a wave pick list for multiple orders
     * This creates a consolidated pick list across orders to improve efficiency
     * 
     * @param array $orderIds Array of order IDs to include in the wave
     * @param int|null $warehouseId Optional warehouse ID to filter by
     * @return array Wave pick list data
     */
    public function generateWavePickList(array $orderIds, ?int $warehouseId = null): array
    {
        // Find all the orders
        $orders = Order::with(['items.product', 'warehouse'])
            ->whereIn('id', $orderIds)
            ->whereIn('status', ['ready_to_pick', 'picking'])
            ->get();
        
        // Filter by warehouse if specified
        if ($warehouseId) {
            $orders = $orders->where('warehouse_id', $warehouseId);
        }
        
        // Check if we have valid orders
        if ($orders->isEmpty()) {
            throw new \Exception("No valid orders found for wave picking.");
        }
        
        // Begin collecting wave pick list data
        $wavePickListData = [
            'wave_id' => uniqid('wave-'),
            'warehouse_id' => $warehouseId ?? $orders->first()->warehouse_id,
            'warehouse_name' => $warehouseId ? $orders->first()->warehouse->name : $orders->first()->warehouse->name,
            'orders' => [],
            'location_groups' => [],
            'generated_at' => now()->toDateTimeString(),
            'generated_by' => Auth::id(),
        ];
        
        // Add order information
        foreach ($orders as $order) {
            $wavePickListData['orders'][] = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ];
            
            // Transition order to picking if it's ready to pick
            if ($order->status === 'ready_to_pick') {
                $order->transitionTo('picking');
            }
        }
        
        // Group items by location for efficient picking
        $locationBasedPicks = [];
        
        // Process each order and its items
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                // Skip items that are already fully picked or cancelled
                if ($item->quantity_picked >= $item->quantity || $item->status === 'cancelled') {
                    continue;
                }
                
                // Get the remaining quantity to pick
                $quantityToPick = $item->quantity - $item->quantity_picked;
                
                // Get allocation details to know where to pick from
                $allocationDetails = $item->allocation_details ?: [];
                
                // If there are no allocation details, add a simple pick instruction
                if (empty($allocationDetails)) {
                    // Create a default location group for unallocated items
                    $defaultLocationId = 'unallocated';
                    
                    if (!isset($locationBasedPicks[$defaultLocationId])) {
                        $locationBasedPicks[$defaultLocationId] = [
                            'location_id' => null,
                            'location_name' => 'Not specified - find in warehouse',
                            'items' => [],
                        ];
                    }
                    
                    // Add this item to the default location group
                    $locationBasedPicks[$defaultLocationId]['items'][] = [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->name,
                        'product_sku' => $item->sku,
                        'quantity' => $quantityToPick,
                        'unit_of_measure' => $item->unit_of_measure,
                        'lot_number' => null,
                    ];
                    
                    continue;
                }
                
                // Process allocated inventory for picking
                $remainingToPick = $quantityToPick;
                
                foreach ($allocationDetails as $allocation) {
                    // Skip if we've already assigned all the quantity
                    if ($remainingToPick <= 0) {
                        break;
                    }
                    
                    // Determine how much to pick from this location
                    $pickQuantity = min($remainingToPick, $allocation['quantity']);
                    
                    // Create a location identifier
                    $locationId = $allocation['location_id'];
                    $locationName = $allocation['bin_location'];
                    
                    // Group by location
                    if (!isset($locationBasedPicks[$locationId])) {
                        $locationBasedPicks[$locationId] = [
                            'location_id' => $locationId,
                            'location_name' => $locationName,
                            'items' => [],
                        ];
                    }
                    
                    // Add this item to the location group
                    $locationBasedPicks[$locationId]['items'][] = [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->name,
                        'product_sku' => $item->sku,
                        'quantity' => $pickQuantity,
                        'unit_of_measure' => $item->unit_of_measure,
                        'lot_number' => $allocation['lot_number'] ?? null,
                    ];
                    
                    // Reduce remaining quantity
                    $remainingToPick -= $pickQuantity;
                }
            }
        }
        
        // Sort location groups by location name for efficient path through warehouse
        $sortedLocationGroups = $locationBasedPicks;
        usort($sortedLocationGroups, function ($a, $b) {
            return strcmp($a['location_name'], $b['location_name']);
        });
        
        // Add location-based groups to the wave pick list
        $wavePickListData['location_groups'] = array_values($sortedLocationGroups);
        
        return $wavePickListData;
    }
}