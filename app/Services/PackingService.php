<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PackingService
{
    /**
     * Record packing for an individual order item
     * This confirms that an item has been packed and is ready for shipping
     * 
     * @param int $orderItemId Order item ID
     * @param array $packingData Additional data about the packing operation
     * @return array Information about the processed packing
     */
    public function packOrderItem(int $orderItemId, array $packingData): array
    {
        // Find the order item
        $orderItem = OrderItem::with('order', 'product')->findOrFail($orderItemId);
        
        // Validate that the order item is in a packable state
        if ($orderItem->status !== 'picked') {
            throw new \Exception("Order item with status '{$orderItem->status}' cannot be packed. Item must be picked first.");
        }
        
        // Get packing data
        $containerType = $packingData['container_type'] ?? null;
        $containerIdentifier = $packingData['container_identifier'] ?? null;
        $notes = $packingData['notes'] ?? null;
        $userId = Auth::id();
        
        // Begin a database transaction
        return DB::transaction(function () use ($orderItem, $containerType, $containerIdentifier, $notes, $userId) {
            // Record the packing on the order item
            $orderItem->recordPacking($containerType, $containerIdentifier, $userId, $notes);
            
            // Check if all items in the order have been packed
            $order = $orderItem->order;
            $allPacked = true;
            
            foreach ($order->items as $item) {
                // Skip cancelled items
                if ($item->status === 'cancelled') {
                    continue;
                }
                
                if ($item->status !== 'packed' && $item->status !== 'shipped') {
                    $allPacked = false;
                    break;
                }
            }
            
            // If all items are packed, transition the order to packed status
            if ($allPacked) {
                $order->transitionTo('packed', $userId);
            }
            
            // Return information about the processed packing
            return [
                'order_item' => $orderItem->refresh(),
                'order' => $order->refresh(),
                'container_type' => $containerType,
                'container_identifier' => $containerIdentifier,
                'packed_by' => $userId,
                'packed_at' => now()->toDateTimeString(),
                'all_items_packed' => $allPacked,
            ];
        });
    }

    /**
     * Pack multiple items for an order in a single transaction
     * This is used when completing packing for an entire order
     * 
     * @param int $orderId Order ID
     * @param array $packingData Array of items to pack and packing information
     * @return array Information about the processed packing
     */
    public function packOrderItems(int $orderId, array $packingData): array
    {
        // Find the order
        $order = Order::with('items')->findOrFail($orderId);
        
        // Validate that the order is in a packable state
        if (!in_array($order->status, ['picked', 'packing'])) {
            throw new \Exception("Order with status '{$order->status}' cannot be packed.");
        }
        
        // Begin a database transaction
        return DB::transaction(function () use ($order, $packingData) {
            $packedItems = [];
            $userId = Auth::id();
            
            // If the order status is picked, transition it to packing
            if ($order->status === 'picked') {
                $order->transitionTo('packing', $userId);
            }
            
            // Process each item in the packing data
            foreach ($packingData['items'] as $itemData) {
                // Validate required fields
                if (!isset($itemData['order_item_id'])) {
                    throw new \Exception("Missing order_item_id for packing operation.");
                }
                
                // Find the order item
                $orderItemId = $itemData['order_item_id'];
                $orderItem = OrderItem::where('id', $orderItemId)
                    ->where('order_id', $order->id)
                    ->firstOrFail();
                
                // Skip items that are already packed or shipped
                if (in_array($orderItem->status, ['packed', 'shipped'])) {
                    continue;
                }
                
                // Validate that the item is picked
                if ($orderItem->status !== 'picked') {
                    throw new \Exception("Order item with status '{$orderItem->status}' cannot be packed. Item must be picked first.");
                }
                
                // Prepare packing data for this item
                $itemPackingData = [
                    'container_type' => $itemData['container_type'] ?? $packingData['container_type'] ?? null,
                    'container_identifier' => $itemData['container_identifier'] ?? $packingData['container_identifier'] ?? null,
                    'notes' => $itemData['notes'] ?? $packingData['notes'] ?? null,
                ];
                
                // Record the packing for this item
                $orderItem->recordPacking(
                    $itemPackingData['container_type'],
                    $itemPackingData['container_identifier'],
                    $userId,
                    $itemPackingData['notes']
                );
                
                // Add to the list of packed items
                $packedItems[] = [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'product_name' => $orderItem->name,
                    'container_type' => $itemPackingData['container_type'],
                    'container_identifier' => $itemPackingData['container_identifier'],
                ];
            }
            
            // Check if all items have been packed
            $allPacked = true;
            foreach ($order->items as $item) {
                // Skip cancelled items
                if ($item->status === 'cancelled') {
                    continue;
                }
                
                if ($item->status !== 'packed' && $item->status !== 'shipped') {
                    $allPacked = false;
                    break;
                }
            }
            
            // If all items are packed, transition the order to packed status
            if ($allPacked) {
                $order->transitionTo('packed', $userId);
            }
            
            // Return information about the processed packing
            return [
                'order' => $order->refresh(),
                'packed_items' => $packedItems,
                'all_items_packed' => $allPacked,
                'packing_details' => [
                    'container_type' => $packingData['container_type'] ?? null,
                    'container_identifier' => $packingData['container_identifier'] ?? null,
                    'notes' => $packingData['notes'] ?? null,
                    'packed_by' => $userId,
                    'packed_at' => now()->toDateTimeString(),
                ],
            ];
        });
    }

    /**
     * Complete the packing process for an order
     * This finalizes the packing and transitions the order to the packed state
     * 
     * @param int $orderId Order ID
     * @param array $completionData Additional data about the packing completion
     * @return array Information about the completed packing
     */
    public function completeOrderPacking(int $orderId, array $completionData = []): array
    {
        // Find the order
        $order = Order::with('items')->findOrFail($orderId);
        
        // Validate that the order is in a packable state
        if (!in_array($order->status, ['packing', 'picked'])) {
            throw new \Exception("Order with status '{$order->status}' cannot complete packing.");
        }
        
        // Begin a database transaction
        return DB::transaction(function () use ($order, $completionData) {
            $userId = Auth::id();
            $notes = $completionData['notes'] ?? null;
            $packingMaterials = $completionData['packing_materials'] ?? [];
            $shippingWeight = $completionData['shipping_weight'] ?? null;
            $dimensions = $completionData['dimensions'] ?? null;
            
            // Check if all items have been packed
            $allPacked = true;
            $packingSummary = [];
            
            foreach ($order->items as $item) {
                // Skip cancelled items
                if ($item->status === 'cancelled') {
                    continue;
                }
                
                // Check if this item is packed
                $isPacked = $item->status === 'packed' || $item->status === 'shipped';
                
                if (!$isPacked && $item->status === 'picked') {
                    // Auto-pack items that are picked but not yet packed
                    $item->recordPacking(
                        $completionData['container_type'] ?? null,
                        $completionData['container_identifier'] ?? null,
                        $userId,
                        $notes
                    );
                    $isPacked = true;
                } else if (!$isPacked) {
                    $allPacked = false;
                }
                
                // Record item packing status in summary
                $packingSummary[] = [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->name,
                    'product_sku' => $item->sku,
                    'status' => $item->status,
                    'is_packed' => $isPacked,
                ];
            }
            
            // Transition the order to packed status
            $order->transitionTo('packed', $userId);
            
            // Update order metadata with packing information
            $metadata = $order->metadata ?: [];
            
            $metadata['packing'] = [
                'completed_at' => now()->toDateTimeString(),
                'completed_by' => $userId,
                'notes' => $notes,
                'packing_materials' => $packingMaterials,
                'shipping_weight' => $shippingWeight,
                'dimensions' => $dimensions,
            ];
            
            $order->metadata = $metadata;
            $order->save();
            
            // If shipping information was provided, automatically transition to awaiting shipment
            if (isset($completionData['ready_for_shipment']) && $completionData['ready_for_shipment']) {
                $order->transitionTo('awaiting_shipment', $userId);
            }
            
            // Return information about the completed packing
            return [
                'order' => $order->refresh(),
                'all_items_packed' => $allPacked,
                'packing_summary' => $packingSummary,
                'completed_by' => $userId,
                'completed_at' => now()->toDateTimeString(),
                'shipping_weight' => $shippingWeight,
                'dimensions' => $dimensions,
                'ready_for_shipment' => $order->status === 'awaiting_shipment',
            ];
        });
    }

    /**
     * Generate packing materials recommendation for an order
     * This suggests appropriate packing materials based on the order items
     * 
     * @param int $orderId Order ID
     * @return array Packing recommendations
     */
    public function generatePackingRecommendation(int $orderId): array
    {
        // Find the order with items and products
        $order = Order::with('items.product')->findOrFail($orderId);
        
        // Initialize recommendations
        $recommendations = [
            'container_recommendations' => [],
            'packing_materials' => [],
            'estimated_weight' => 0,
            'estimated_dimensions' => null,
            'special_handling' => [],
        ];
        
        // Group items by their characteristics for container recommendations
        $smallItems = [];
        $mediumItems = [];
        $largeItems = [];
        $fragileItems = [];
        $heavyItems = [];
        
        // Weight thresholds in kg
        $heavyThreshold = 10; // Items over 10kg are considered heavy
        
        // Process each item
        $totalWeight = 0;
        
        foreach ($order->items as $item) {
            // Skip cancelled items
            if ($item->status === 'cancelled') {
                continue;
            }
            
            $product = $item->product;
            
            // Calculate total weight
            $itemWeight = 0;
            if (isset($product->weight)) {
                $itemWeight = $product->weight * $item->quantity;
                $totalWeight += $itemWeight;
            }
            
            // Check if item is heavy
            if ($itemWeight > $heavyThreshold) {
                $heavyItems[] = [
                    'order_item_id' => $item->id,
                    'product_name' => $item->name,
                    'weight' => $itemWeight,
                ];
            }
            
            // Check for fragile items
            $isFragile = false;
            if (isset($product->attributes) && is_array($product->attributes)) {
                if (isset($product->attributes['is_fragile']) && $product->attributes['is_fragile']) {
                    $isFragile = true;
                    $fragileItems[] = [
                        'order_item_id' => $item->id,
                        'product_name' => $item->name,
                    ];
                }
            }
            
            // Categorize by size if dimensions are available
            if (isset($product->dimensions) && is_array($product->dimensions)) {
                $volume = ($product->dimensions['length'] ?? 0) * 
                          ($product->dimensions['width'] ?? 0) * 
                          ($product->dimensions['height'] ?? 0);
                
                if ($volume < 1000) { // Less than 1000 cubic cm
                    $smallItems[] = [
                        'order_item_id' => $item->id,
                        'product_name' => $item->name,
                        'volume' => $volume,
                    ];
                } else if ($volume < 8000) { // Less than 8000 cubic cm
                    $mediumItems[] = [
                        'order_item_id' => $item->id,
                        'product_name' => $item->name,
                        'volume' => $volume,
                    ];
                } else {
                    $largeItems[] = [
                        'order_item_id' => $item->id,
                        'product_name' => $item->name,
                        'volume' => $volume,
                    ];
                }
            }
        }
        
        // Make container recommendations
        if (count($largeItems) > 0) {
            $recommendations['container_recommendations'][] = [
                'container_type' => 'Large Box',
                'suitable_for' => 'Large items or orders with many items',
                'items' => $largeItems,
            ];
        } else if (count($mediumItems) > 0) {
            $recommendations['container_recommendations'][] = [
                'container_type' => 'Medium Box',
                'suitable_for' => 'Medium-sized items or orders with several items',
                'items' => $mediumItems,
            ];
        } else if (count($smallItems) > 0) {
            $recommendations['container_recommendations'][] = [
                'container_type' => 'Small Box',
                'suitable_for' => 'Small items or orders with few items',
                'items' => $smallItems,
            ];
            
            // For very small items, also suggest padded envelope
            if (count($largeItems) === 0 && count($mediumItems) === 0 && count($fragileItems) === 0) {
                $recommendations['container_recommendations'][] = [
                    'container_type' => 'Padded Envelope',
                    'suitable_for' => 'Small, non-fragile items',
                    'items' => $smallItems,
                ];
            }
        }
        
        // If no specific recommendations, provide a default
        if (empty($recommendations['container_recommendations'])) {
            $recommendations['container_recommendations'][] = [
                'container_type' => 'Standard Box',
                'suitable_for' => 'General purpose packing',
            ];
        }
        
        // Recommend packing materials
        $recommendations['packing_materials'] = ['Packing Tape'];
        
        if (count($fragileItems) > 0) {
            $recommendations['packing_materials'][] = 'Bubble Wrap';
            $recommendations['packing_materials'][] = 'Fragile Stickers';
            $recommendations['special_handling'][] = 'Contains fragile items - handle with care';
        }
        
        // Always recommend filler material
        $recommendations['packing_materials'][] = 'Packing Paper';
        
        if (count($heavyItems) > 0) {
            $recommendations['packing_materials'][] = 'Reinforced Tape';
            $recommendations['special_handling'][] = 'Contains heavy items - use proper lifting techniques';
        }
        
        // Set estimated weight
        $recommendations['estimated_weight'] = $totalWeight;
        
        // Attempt to estimate dimensions based on items
        // This is a simplified approach and would need refinement in a real system
        if (count($largeItems) > 0) {
            $recommendations['estimated_dimensions'] = ['length' => 60, 'width' => 40, 'height' => 40]; // cm
        } else if (count($mediumItems) > 0) {
            $recommendations['estimated_dimensions'] = ['length' => 40, 'width' => 30, 'height' => 30]; // cm
        } else if (count($smallItems) > 0) {
            $recommendations['estimated_dimensions'] = ['length' => 25, 'width' => 20, 'height' => 15]; // cm
        }
        
        return $recommendations;
    }
}