<?php

namespace App\Services\Inventory;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FifoInventoryStrategy extends BaseInventoryStrategy
{
    /**
     * Receive inventory.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function receive(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $quantity, $attributes, $referenceType, $referenceId) {
            // Create inventory movement
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => null, // No source location for receiving
                'to_location_id' => $location->id,
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? 'EA',
                'lot_number' => $attributes['lot_number'] ?? null,
                'batch_number' => $attributes['batch_number'] ?? null,
                'movement_type' => InventoryMovement::TYPE_RECEIVE,
                'reason' => $attributes['reason'] ?? 'Goods receipt',
            ]);

            // Find or create inventory record
            $inventory = $this->findOrCreateInventory($product, $location, $attributes);
            
            // Update inventory quantity
            $this->updateInventoryQuantity($inventory, $quantity, 'increment');
            
            // Create transaction record
            $transaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_INCREMENT,
                'product_id' => $product->id,
                'location_id' => $location->id,
                'quantity' => $quantity,
                'unit_cost' => $attributes['unit_cost'] ?? $product->cost,
                'total_cost' => ($attributes['unit_cost'] ?? $product->cost) * $quantity,
                'lot_number' => $attributes['lot_number'] ?? null,
                'batch_number' => $attributes['batch_number'] ?? null,
            ]);
            
            return [
                'movement' => $movement,
                'inventory' => $inventory,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Transfer inventory from one location to another.
     * 
     * @param Product $product
     * @param BinLocation $fromLocation
     * @param BinLocation $toLocation
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function transfer(
        Product $product,
        BinLocation $fromLocation,
        BinLocation $toLocation,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $fromLocation, $toLocation, $quantity, $attributes, $referenceType, $referenceId) {
            // Validate sufficient inventory
            $sourceInventory = Inventory::where('product_id', $product->id)
                ->where('location_id', $fromLocation->id)
                ->where(function ($query) use ($attributes) {
                    if (isset($attributes['lot_number'])) {
                        $query->where('lot_number', $attributes['lot_number']);
                    }
                    if (isset($attributes['batch_number'])) {
                        $query->where('batch_number', $attributes['batch_number']);
                    }
                })
                ->first();

            if (!$sourceInventory || $sourceInventory->available_quantity < $quantity) {
                throw new Exception("Insufficient available inventory for transfer");
            }

            // Create movement record
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? $sourceInventory->unit_of_measure,
                'lot_number' => $attributes['lot_number'] ?? $sourceInventory->lot_number,
                'batch_number' => $attributes['batch_number'] ?? $sourceInventory->batch_number,
                'movement_type' => InventoryMovement::TYPE_TRANSFER,
                'reason' => $attributes['reason'] ?? 'Inventory transfer',
            ]);

            // Decrease quantity from source location
            $this->updateInventoryQuantity($sourceInventory, $quantity, 'decrement');

            // Create source transaction
            $sourceTransaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_DECREMENT,
                'product_id' => $product->id,
                'location_id' => $fromLocation->id,
                'quantity' => $quantity,
                'lot_number' => $sourceInventory->lot_number,
                'batch_number' => $sourceInventory->batch_number,
            ]);

            // Find or create destination inventory
            $destInventory = $this->findOrCreateInventory($product, $toLocation, [
                'lot_number' => $sourceInventory->lot_number,
                'batch_number' => $sourceInventory->batch_number,
                'unit_of_measure' => $sourceInventory->unit_of_measure,
                'received_date' => $sourceInventory->received_date,
                'expiry_date' => $sourceInventory->expiry_date,
            ]);

            // Increase quantity at destination
            $this->updateInventoryQuantity($destInventory, $quantity, 'increment');

            // Create destination transaction
            $destTransaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_INCREMENT,
                'product_id' => $product->id,
                'location_id' => $toLocation->id,
                'quantity' => $quantity,
                'lot_number' => $sourceInventory->lot_number,
                'batch_number' => $sourceInventory->batch_number,
            ]);

            return [
                'movement' => $movement,
                'source_inventory' => $sourceInventory,
                'destination_inventory' => $destInventory,
                'transactions' => [$sourceTransaction, $destTransaction],
            ];
        });
    }

    /**
     * Pick inventory from a location using FIFO strategy.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function pick(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $quantity, $attributes, $referenceType, $referenceId) {
            // Get inventory items ordered by received date (FIFO)
            $inventoryItems = Inventory::where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->where('available_quantity', '>', 0)
                ->when(isset($attributes['lot_number']), function ($query) use ($attributes) {
                    return $query->where('lot_number', $attributes['lot_number']);
                })
                ->when(isset($attributes['batch_number']), function ($query) use ($attributes) {
                    return $query->where('batch_number', $attributes['batch_number']);
                })
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();

            // Check if we have enough inventory
            $totalAvailable = $inventoryItems->sum('available_quantity');
            if ($totalAvailable < $quantity) {
                throw new Exception("Insufficient available inventory for picking");
            }

            // Create movement record
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => $location->id,
                'to_location_id' => null, // No destination for picking
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? 'EA',
                'movement_type' => InventoryMovement::TYPE_PICK,
                'reason' => $attributes['reason'] ?? 'Order picking',
            ]);

            // Track FIFO layers
            $fifoLayers = [];
            $remainingQuantity = $quantity;
            $transactions = [];

            // Pick from each inventory item until the required quantity is met
            foreach ($inventoryItems as $item) {
                $pickQuantity = min($remainingQuantity, $item->available_quantity);
                
                // Update inventory
                $this->updateInventoryQuantity($item, $pickQuantity, 'decrement');

                // Create transaction
                $transaction = $this->createTransaction($movement, [
                    'transaction_type' => InventoryTransaction::TYPE_DECREMENT,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => $pickQuantity,
                    'unit_cost' => $attributes['unit_cost'] ?? $product->cost,
                    'total_cost' => ($attributes['unit_cost'] ?? $product->cost) * $pickQuantity,
                    'lot_number' => $item->lot_number,
                    'batch_number' => $item->batch_number,
                ]);

                $transactions[] = $transaction;
                
                // Record FIFO layer
                $fifoLayers[] = [
                    'inventory_id' => $item->id,
                    'lot_number' => $item->lot_number,
                    'batch_number' => $item->batch_number,
                    'quantity' => $pickQuantity,
                    'received_date' => $item->received_date ? $item->received_date->format('Y-m-d') : null,
                ];

                $remainingQuantity -= $pickQuantity;
                
                // Stop if we've picked enough
                if ($remainingQuantity <= 0) {
                    break;
                }
            }

            // Update the movement with FIFO layers
            $movement->fifo_layers = $fifoLayers;
            $movement->save();

            return [
                'movement' => $movement,
                'transactions' => $transactions,
                'fifo_layers' => $fifoLayers,
            ];
        });
    }

    /**
     * Adjust inventory quantity.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $newQuantity
     * @param array $attributes
     * @param string|null $reason
     * @return array
     */
    public function adjust(
        Product $product,
        BinLocation $location,
        float $newQuantity,
        array $attributes = [],
        ?string $reason = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $newQuantity, $attributes, $reason) {
            // Find inventory record
            $inventory = $this->findOrCreateInventory($product, $location, $attributes);
            
            // Calculate difference
            $difference = $newQuantity - $inventory->quantity;
            $movementType = $difference >= 0 ? InventoryMovement::TYPE_ADJUST : InventoryMovement::TYPE_ADJUST;
            
            // Create movement record
            $movement = $this->createMovement([
                'product_id' => $product->id,
                'from_location_id' => $difference < 0 ? $location->id : null,
                'to_location_id' => $difference >= 0 ? $location->id : null,
                'quantity' => abs($difference),
                'unit_of_measure' => $inventory->unit_of_measure,
                'lot_number' => $inventory->lot_number,
                'batch_number' => $inventory->batch_number,
                'movement_type' => $movementType,
                'reason' => $reason ?? 'Inventory adjustment',
            ]);
            
            // Update inventory quantity
            $this->updateInventoryQuantity($inventory, $newQuantity, 'set');
            
            // Create transaction record
            $transaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_ADJUST,
                'product_id' => $product->id,
                'location_id' => $location->id,
                'quantity' => $newQuantity,
                'unit_cost' => $attributes['unit_cost'] ?? $product->cost,
                'total_cost' => ($attributes['unit_cost'] ?? $product->cost) * $newQuantity,
                'lot_number' => $inventory->lot_number,
                'batch_number' => $inventory->batch_number,
            ]);
            
            return [
                'movement' => $movement,
                'inventory' => $inventory,
                'transaction' => $transaction,
                'difference' => $difference,
            ];
        });
    }

    /**
     * Reserve inventory.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function reserve(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $quantity, $attributes, $referenceType, $referenceId) {
            // Get inventory items ordered by received date (FIFO)
            $inventoryItems = Inventory::where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->where('available_quantity', '>', 0)
                ->when(isset($attributes['lot_number']), function ($query) use ($attributes) {
                    return $query->where('lot_number', $attributes['lot_number']);
                })
                ->when(isset($attributes['batch_number']), function ($query) use ($attributes) {
                    return $query->where('batch_number', $attributes['batch_number']);
                })
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();

            // Check if we have enough inventory
            $totalAvailable = $inventoryItems->sum('available_quantity');
            if ($totalAvailable < $quantity) {
                throw new Exception("Insufficient available inventory for reservation");
            }

            // Create movement record
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => $location->id,
                'to_location_id' => $location->id, // Same location for reservation
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? 'EA',
                'movement_type' => 'reserve', // Not a standard movement type
                'reason' => $attributes['reason'] ?? 'Inventory reservation',
            ]);

            // Track FIFO layers
            $fifoLayers = [];
            $remainingQuantity = $quantity;
            $transactions = [];

            // Reserve from each inventory item until the required quantity is met
            foreach ($inventoryItems as $item) {
                $reserveQuantity = min($remainingQuantity, $item->available_quantity);
                
                // Update inventory reserved quantity
                $this->updateInventoryQuantity($item, $reserveQuantity, 'reserve');

                // Create transaction
                $transaction = $this->createTransaction($movement, [
                    'transaction_type' => InventoryTransaction::TYPE_RESERVE,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => $reserveQuantity,
                    'lot_number' => $item->lot_number,
                    'batch_number' => $item->batch_number,
                ]);

                $transactions[] = $transaction;
                
                // Record FIFO layer
                $fifoLayers[] = [
                    'inventory_id' => $item->id,
                    'lot_number' => $item->lot_number,
                    'batch_number' => $item->batch_number,
                    'quantity' => $reserveQuantity,
                    'received_date' => $item->received_date ? $item->received_date->format('Y-m-d') : null,
                ];

                $remainingQuantity -= $reserveQuantity;
                
                // Stop if we've reserved enough
                if ($remainingQuantity <= 0) {
                    break;
                }
            }

            // Update the movement with FIFO layers
            $movement->fifo_layers = $fifoLayers;
            $movement->save();

            return [
                'movement' => $movement,
                'transactions' => $transactions,
                'fifo_layers' => $fifoLayers,
            ];
        });
    }

    /**
     * Unreserve inventory.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function unreserve(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $quantity, $attributes, $referenceType, $referenceId) {
            // Get inventory items with reservations, ordered by received date (FIFO)
            $inventoryItems = Inventory::where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->where('reserved_quantity', '>', 0)
                ->when(isset($attributes['lot_number']), function ($query) use ($attributes) {
                    return $query->where('lot_number', $attributes['lot_number']);
                })
                ->when(isset($attributes['batch_number']), function ($query) use ($attributes) {
                    return $query->where('batch_number', $attributes['batch_number']);
                })
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();

            // Check if we have enough reserved inventory
            $totalReserved = $inventoryItems->sum('reserved_quantity');
            if ($totalReserved < $quantity) {
                throw new Exception("Insufficient reserved inventory for unreservation");
            }

            // Create movement record
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => $location->id,
                'to_location_id' => $location->id, // Same location for unreservation
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? 'EA',
                'movement_type' => 'unreserve', // Not a standard movement type
                'reason' => $attributes['reason'] ?? 'Inventory unreservation',
            ]);

            // Track FIFO layers
            $fifoLayers = [];
            $remainingQuantity = $quantity;
            $transactions = [];

            // Unreserve from each inventory item until the required quantity is met
            foreach ($inventoryItems as $item) {
                $unreserveQuantity = min($remainingQuantity, $item->reserved_quantity);
                
                // Update inventory reserved quantity
                $this->updateInventoryQuantity($item, $unreserveQuantity, 'unreserve');

                // Create transaction
                $transaction = $this->createTransaction($movement, [
                    'transaction_type' => InventoryTransaction::TYPE_UNRESERVE,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => $unreserveQuantity,
                    'lot_number' => $item->lot_number,
                    'batch_number' => $item->batch_number,
                ]);

                $transactions[] = $transaction;
                
                // Record FIFO layer
                $fifoLayers[] = [
                    'inventory_id' => $item->id,
                    'lot_number' => $item->lot_number,
                    'batch_number' => $item->batch_number,
                    'quantity' => $unreserveQuantity,
                    'received_date' => $item->received_date ? $item->received_date->format('Y-m-d') : null,
                ];

                $remainingQuantity -= $unreserveQuantity;
                
                // Stop if we've unreserved enough
                if ($remainingQuantity <= 0) {
                    break;
                }
            }

            // Update the movement with FIFO layers
            $movement->fifo_layers = $fifoLayers;
            $movement->save();

            return [
                'movement' => $movement,
                'transactions' => $transactions,
                'fifo_layers' => $fifoLayers,
            ];
        });
    }
}