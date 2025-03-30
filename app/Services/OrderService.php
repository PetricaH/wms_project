<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Create a new order in the system
     * This is the main method for creating customer orders
     * 
     * @param array $orderData Order header data
     * @param array $itemsData Array of order line items
     * @return Order The created order
     */
    public function createOrder(array $orderData, array $itemsData): Order
    {
        // Begin a database transaction to ensure all operations succeed or fail together
        return DB::transaction(function () use ($orderData, $itemsData) {
            // Generate a unique order number if not provided
            if (empty($orderData['order_number'])) {
                $orderData['order_number'] = Order::generateOrderNumber();
            }
            
            // Set default order date to today if not provided
            if (empty($orderData['order_date'])) {
                $orderData['order_date'] = Carbon::today();
            }
            
            // Create the order record
            $order = new Order($orderData);
            $order->save();
            
            // Create each order item
            $lineNumber = 1;
            foreach ($itemsData as $itemData) {
                // Find the product to get current information
                $product = Product::findOrFail($itemData['product_id']);
                
                // Create the order item with product information
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'line_number' => $itemData['line_number'] ?? $lineNumber++,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'description' => $product->description,
                    'quantity' => $itemData['quantity'],
                    'unit_of_measure' => $itemData['unit_of_measure'] ?? 'ea',
                    'unit_price' => $itemData['unit_price'] ?? $product->price,
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                
                // Calculate financial fields
                $orderItem->subtotal = $orderItem->quantity * $orderItem->unit_price;
                
                // Apply discount if applicable
                if ($orderItem->discount_percentage > 0) {
                    $orderItem->discount_amount = $orderItem->subtotal * ($orderItem->discount_percentage / 100);
                    $orderItem->subtotal -= $orderItem->discount_amount;
                }
                
                // Calculate tax
                $orderItem->tax_amount = $orderItem->subtotal * ($orderItem->tax_rate / 100);
                
                // Calculate total
                $orderItem->total = $orderItem->subtotal + $orderItem->tax_amount;
                
                $orderItem->save();
            }
            
            // Update order totals
            $order->updateTotals();
            
            return $order;
        });
    }

    /**
     * Process payment for an order
     * This updates the payment status and records payment information
     * 
     * @param int $orderId Order ID
     * @param array $paymentData Payment data (method, reference, amount)
     * @return Order The updated order
     */
    public function processPayment(int $orderId, array $paymentData): Order
    {
        // Find the order
        $order = Order::findOrFail($orderId);
        
        // Begin a database transaction
        return DB::transaction(function () use ($order, $paymentData) {
            // Update payment information
            $order->payment_method = $paymentData['method'] ?? $order->payment_method;
            $order->payment_reference = $paymentData['reference'] ?? $order->payment_reference;
            
            // Update payment status
            $order->payment_status = 'paid';
            
            // If order was awaiting payment, transition to paid status
            if ($order->status === 'awaiting_payment') {
                $order->transitionTo('paid');
            }
            
            $order->save();
            
            // Add to order metadata for audit trail
            $paymentRecord = [
                'date' => now()->toDateTimeString(),
                'method' => $paymentData['method'] ?? null,
                'reference' => $paymentData['reference'] ?? null,
                'amount' => $paymentData['amount'] ?? $order->total_amount,
                'processor' => $paymentData['processor'] ?? null,
                'user_id' => Auth::id(),
            ];
            
            // Initialize metadata as an empty array if it's null
            $metadata = $order->metadata ?: [];
            
            // Add payment records
            if (!isset($metadata['payments'])) {
                $metadata['payments'] = [];
            }
            
            $metadata['payments'][] = $paymentRecord;
            $order->metadata = $metadata;
            
            $order->save();
            
            return $order;
        });
    }

    /**
     * Process inventory allocation for an order
     * This reserves inventory for the order items
     * 
     * @param int $orderId Order ID
     * @return array Result of the allocation process
     */
    public function allocateOrderInventory(int $orderId): array
    {
        // Find the order
        $order = Order::with('items')->findOrFail($orderId);
        
        // Check if the order is in a state where allocation is allowed
        if (!in_array($order->status, ['pending', 'paid', 'processing'])) {
            throw new \Exception("Order status '{$order->status}' does not allow inventory allocation.");
        }
        
        // Begin a database transaction
        return DB::transaction(function () use ($order) {
            // Perform allocation
            $allocationResults = $order->allocateInventory();
            
            return $allocationResults;
        });
    }

    /**
     * Cancel an order
     * This marks the order and its items as cancelled
     * 
     * @param int $orderId Order ID
     * @param string $reason Reason for cancellation
     * @return Order The cancelled order
     */
    public function cancelOrder(int $orderId, string $reason): Order
    {
        // Find the order
        $order = Order::with('items')->findOrFail($orderId);
        
        // Check if the order can be cancelled
        if (in_array($order->status, ['shipped', 'delivered', 'completed'])) {
            throw new \Exception("Cannot cancel an order with status '{$order->status}'.");
        }
        
        // Begin a database transaction
        return DB::transaction(function () use ($order, $reason) {
            // Get the current user ID
            $userId = Auth::id();
            
            // Cancel each order item
            foreach ($order->items as $item) {
                // Only cancel items that haven't been shipped
                if ($item->quantity_shipped <= 0) {
                    $item->cancel($userId, $reason);
                }
            }
            
            // Transition the order to cancelled status
            $order->transitionTo('cancelled', $userId, $reason);
            
            return $order->refresh();
        });
    }

    /**
     * Get orders that are ready for picking
     * This returns orders that have been fully allocated
     * 
     * @param int|null $warehouseId Optional warehouse ID to filter by
     * @return \Illuminate\Database\Eloquent\Collection Collection of orders
     */
    public function getOrdersReadyForPicking(?int $warehouseId = null)
    {
        $query = Order::with('items', 'warehouse')
            ->where('status', 'ready_to_pick');
        
        // Filter by warehouse if specified
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        // Sort by due date (oldest first)
        return $query->orderBy('due_date')->get();
    }
    
    /**
     * Assign an order to a user for processing
     * This updates the assigned_to field on the order
     * 
     * @param int $orderId Order ID
     * @param int $userId User ID
     * @return Order The updated order
     */
    public function assignOrderToUser(int $orderId, int $userId): Order
    {
        // Find the order
        $order = Order::findOrFail($orderId);
        
        // Find the user
        $user = User::findOrFail($userId);
        
        // Update the assignment
        $order->assigned_to = $userId;
        $order->save();
        
        return $order;
    }

    /**
     * Generate a pick list for an order
     * This creates a structured list of items to be picked
     * 
     * @param int $orderId Order ID
     * @return array Pick list data
     */
    public function generatePickList(int $orderId): array
    {
        // Find the order with related data
        $order = Order::with([
            'items.product', 
            'warehouse',
            'assignedUser'
        ])->findOrFail($orderId);
        
        // Check if the order is in a pickable state
        if (!in_array($order->status, ['ready_to_pick', 'picking'])) {
            throw new \Exception("Order status '{$order->status}' is not ready for picking.");
        }
        
        // Begin collecting pick list data
        $pickListData = [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_date' => $order->order_date->format('Y-m-d'),
                'status' => $order->status,
            ],
            'warehouse' => [
                'id' => $order->warehouse->id,
                'name' => $order->warehouse->name,
                'code' => $order->warehouse->code,
            ],
            'assigned_to' => $order->assignedUser ? [
                'id' => $order->assignedUser->id,
                'name' => $order->assignedUser->name,
            ] : null,
            'pick_items' => [],
            'generated_at' => now()->toDateTimeString(),
            'generated_by' => Auth::id(),
        ];
        
        // Group items by location for efficient picking
        $locationBasedPicks = [];
        
        // Process each order item
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
                $pickListData['pick_items'][] = [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->name,
                    'product_sku' => $item->sku,
                    'quantity' => $quantityToPick,
                    'unit_of_measure' => $item->unit_of_measure,
                    'location' => 'Not specified - find in warehouse',
                    'location_id' => null,
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
        
        // Convert location groups to a flat list if needed
        foreach ($locationBasedPicks as $locationGroup) {
            foreach ($locationGroup['items'] as $item) {
                $pickListData['pick_items'][] = [
                    'order_item_id' => $item['order_item_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'quantity' => $item['quantity'],
                    'unit_of_measure' => $item['unit_of_measure'],
                    'location' => $locationGroup['location_name'],
                    'location_id' => $locationGroup['location_id'],
                    'lot_number' => $item['lot_number'],
                ];
            }
        }
        
        // Add location-based groups for structured picking
        $pickListData['location_groups'] = array_values($locationBasedPicks);
        
        // If the order status is ready_to_pick, transition it to picking
        if ($order->status === 'ready_to_pick') {
            $order->transitionTo('picking');
        }
        
        return $pickListData;
    }
}