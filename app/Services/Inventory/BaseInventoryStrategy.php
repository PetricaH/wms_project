<?php

namespace App\Services\Inventory;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

abstract class BaseInventoryStrategy implements InventoryStrategyInterface
{
    /**
     * Create a new inventory movement record.
     * 
     * @param array $data
     * @return InventoryMovement
     */
    protected function createMovement(array $data): InventoryMovement
    {
        return InventoryMovement::create(array_merge($data, [
            'performed_by' => Auth::id(),
        ]));
    }

    /**
     * Create a new inventory transaction record.
     * 
     * @param InventoryMovement $movement
     * @param array $data
     * @return InventoryTransaction
     */
    protected function createTransaction(InventoryMovement $movement, array $data): InventoryTransaction
    {
        // Get current running balance
        $currentBalance = InventoryTransaction::where('product_id', $data['product_id'])
            ->where('location_id', $data['location_id'])
            ->latest('id')
            ->value('running_balance') ?? 0;

        // Calculate new running balance based on transaction type
        $newBalance = $currentBalance;
        switch ($data['transaction_type']) {
            case InventoryTransaction::TYPE_INCREMENT:
                $newBalance += $data['quantity'];
                break;
            case InventoryTransaction::TYPE_DECREMENT:
                $newBalance -= $data['quantity'];
                break;
            case InventoryTransaction::TYPE_ADJUST:
                $newBalance = $data['quantity']; // Set to the new quantity directly
                break;
            // Reserve and unreserve don't affect the running balance
        }

        return $movement->transactions()->create(array_merge($data, [
            'running_balance' => $newBalance,
            'performed_by' => Auth::id(),
        ]));
    }

    /**
     * Find or create an inventory record for the given product and location.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param array $attributes
     * @return Inventory
     */
    protected function findOrCreateInventory(Product $product, BinLocation $location, array $attributes = []): Inventory
    {
        $lotNumber = $attributes['lot_number'] ?? null;
        $batchNumber = $attributes['batch_number'] ?? null;
        $unitOfMeasure = $attributes['unit_of_measure'] ?? 'EA';
        
        return Inventory::firstOrCreate(
            [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'lot_number' => $lotNumber,
                'batch_number' => $batchNumber,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'unit_of_measure' => $unitOfMeasure,
                'received_date' => $attributes['received_date'] ?? now(),
                'expiry_date' => $attributes['expiry_date'] ?? null,
            ]
        );
    }

    /**
     * Update inventory quantity.
     * 
     * @param Inventory $inventory
     * @param float $quantity
     * @param string $operation
     * @return Inventory
     */
    protected function updateInventoryQuantity(Inventory $inventory, float $quantity, string $operation): Inventory
    {
        switch ($operation) {
            case 'increment':
                $inventory->quantity += $quantity;
                break;
            case 'decrement':
                $inventory->quantity -= $quantity;
                break;
            case 'set':
                $inventory->quantity = $quantity;
                break;
            case 'reserve':
                $inventory->reserved_quantity += $quantity;
                break;
            case 'unreserve':
                $inventory->reserved_quantity -= $quantity;
                break;
        }

        // Auto-calculate available quantity
        $inventory->available_quantity = $inventory->quantity - $inventory->reserved_quantity;
        
        $inventory->save();
        
        return $inventory;
    }

    /**
     * Get entity ID safely, even if entity is null.
     * 
     * @param mixed $entity
     * @return int|null
     */
    protected function getEntityId($entity): ?int
    {
        return $entity ? $entity->id : null;
    }

    /**
     * Execute a database transaction.
     * 
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    protected function executeTransaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}