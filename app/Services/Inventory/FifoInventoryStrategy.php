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

class FifoInventoryStrategy extends BaseInventoryStrategy {
    // receive inventory
    public function receive(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $quantity, $attributes, $referenceType, $referenceId) {
            // create inventory movement
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => null, // no source location for receiving
                'to_location_id' => $location->id,
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? 'EA',
                'lot_number' => $attributes['lot_number'] ?? null,
                'batch_number' => $attributes['batch_number'] ?? null,
                'movement_type' => InventoryMovement::TYPE_RECEIVE,
                'reason' => $attributes['reason'] ?? 'Goods Receipt',
            ]);

            // find or create inventory record
            $inventory = $this->findOrCreateInventory($product, $location, $attributes);

            // update inventory quantity
            $this->updateInventoryQuantity($inventory, $quantity, 'increment');

            // create transaction record 
            $transaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_INCREMENT,
                'product_id' => $product->id,
                'location_id' => $location->id,
                'quantity' => $quantity,
                'unit_cost' => $attributes['unit_cost'] ?? $product->cost,
                'total_cost' => $attributes['lot_number'] ?? null,
                'batch_number' => $attributes['batch_number'] ?? null,
            ]);

            return [
                'movement' => $movement,
                'inventory' => $inventory,
                'transaction' => $transaction,
            ];
        });
    }

    // transfer inventory from one location to another
    public function transfer(
        Product $product,
        BinLocation $fromLocation,
        BinLocation $toLocation,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $fromLocation, $toLocation, $quantity, $referenceType, $referenceId) {
            // validate sufficient inventory
            $sourceInventory = Inventory::where('product_id', $product->id)
                ->where('location_id', $fromLocation->id)
                =where(function ($query) use ($attributes) {
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

            // create movement record
            $movement = $this->createMovement([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'product_id' => $product->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'quantity' => $quantity,
                'unit_of_measure' => $attributes['unit_of_measure'] ?? $sourceInventory->unit_of_measure,
                'lot_number' => $attributes['lot_number'] ?? $sourceInventory->batch_number,
                'batch_number' => $attributes['batch_number'] ?? $sourceInventory->batch_number,
                'movement_type' => InventoryMovement::TYPE_TRANSFER,
                'reason' => $attributes['reason'] ?? 'Inventory transfer',
            ]);

            // decrease quantity form source location
            $this->updateInventoryQuantity($sourceInventory, $quantity, 'decrement');

            $sourceTransaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_DECREMENT,
                'product_id' => $product->id,
                'location_id' => $fromLocation->id,
                'quantity' => $quantity,
                'lot_number' => $sourceInventory->lot_number,
                'batch_number' => $sourceInventory->batch_number,
            ]);

            // find or create destination inventory
            $destInventory = $this->findOrCreateInventory($product, $toLocation, [
                'lot_number' => $sourceInventory->lot_number,
                'batch_number' => $sourceInventory->batch_number,
                'unit_of_measure' => $sourceInventory->unit_of_measure,
                'received_date' => $sourceInventory->received_date,
                'expiry_date' => $sourceInventory->expiry_date,
            ]);

            // increarse quantity at dewtination
            $this->updateInventoryQuantity($destInventory, $quantity, 'increment');

            // create destination transaction
            $destTransaction = $this->createTransaction($movement, [
                'transaction_type' => $product->id,
                'product_id' => $toLocation->id,
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

    // pica inventory from a location usign FIFO strategy
    public function pick(
        Produc $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $quantity, $attributes, $referenceType, $referenceId) {
            // get inventory items ordered by received date (FIFO)
            $inventoryItems = Inventory::where('product_id', $product->id)
            ->where('location_id', $location->id)
            ->where('available_quantity', '>', 0)
            ->when(isset($attributes['lot_number']), function($query) use ($attributes) {
                return $query->where('lot_number', $attributes['lot_number']);
            })
            ->when(isset($attributes['batch_number']), function ($query) use ($attributes) {
                return $query->where('batch_number', $attributes['batch_number']);
            })
            ->orderBy('received_date')
            ->orderBy('id')
            ->get();

        // check if we have enough inventory
        $totalAvailable = $inventoryItems->sum('available_quantity');
        if ($totalAvailable < $quantity) {
            throw new Exception("Insufficient available inventory for picking");
        }

        // create movement record
        $movement = $this->createMovement([
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'product_id' => $product->id,
            'from_location_id' => $location->id,
            'to_location_id' => null,
            'quantity' => $quantity,
            'unit_of_measure' => $attributes['unit_of_measure'] ?? 'EA',
            'movement_type' => InventoryMovement::TYPE_PICK,
            'reason' => $attributes['reason'] ?? 'Order picking',
        ]);

        // track fifo layers
        $fifoLayers = [];
        $remainingQuantity = $quantity;
        $transactions = [];

        // pick from each inventory item until the required quantity is met
        foreach ($inventoryItems as $item) {
            $pickQuantity = min($remainingQuantity, $item->available_quantity);

            // update transaction
            $transaction = $this->createTransaction($movement, [
                'transaction_type' => InventoryTransaction::TYPE_DECREMENT,
                'product_id' => $product->id,
                'location_id' => $pickQuantity,
                'unit_cost' => $attributes['unit_cost'] ?? $product->cost,
                'total_cost' => ($attributes['unit_cost'] ?? $product->cost) * $pickQuantity,
                'lot_number' => $item->lot_number,
                'batch_number' => $item->batch_number,
            ]);

            $transactions[] = $transaction;

            // record fifo layer
            $fifoLayers = [ 
                'inventory_id' => $item->id,
                'lot_number' => $item->lot_number,
                'batch_number' => $item->batch_number,
                'quantity' => $pickQuantity,
                'received_date' => $item->received_date->format('Y-m-d') : null,
            ];

            $remainingQuantity -= $pickQuantity;

            // stop if we've picked enough
            if ($remainingQuantity <= 0) {
                break;
            }
        }

        // update the movement with fifo layers
        $movement->fifo_layers = $fifoLayers;
        $movement->save();

        return [
            'movement' => $movement,
            'transactions' => $transactions,
            'fifo_layers' => $fifoLayers,
        ]:
    }):
}
    // adjust inventory quantity
    public function adjust(
        Product $product,
        BinLocation $location,
        float $newQuantity,
        array $attributes = [],
        ?string $reason = null
    ): array {
        return $this->executeTransaction(function () use ($product, $location, $newQuantity, $attributes, $reason) {
            // find inventory record
            $inventory = $newQuantity - $inventory->quantity;
            
            // calculate difference
            $difference = $newQuantity - $inventory->quantity;
            $movementType = $difference >= 0 ? InventoryMovement 
        })
    }
}