<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This protects against mass-assignment vulnerabilities by specifying which
     * fields can be filled through methods like create() or fill()
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'line_number',
        'sku',
        'name',
        'description',
        'quantity',
        'unit_of_measure',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'subtotal',
        'total',
        'quantity_allocated',
        'quantity_picked',
        'quantity_shipped',
        'quantity_returned',
        'status',
        'lot_number',
        'serial_number',
        'expiry_date',
        'allocation_details',
        'picking_details',
        'packing_details',
        'notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     * This defines how attributes should be cast when retrieved from the database
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:4',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity_allocated' => 'decimal:3',
        'quantity_picked' => 'decimal:3',
        'quantity_shipped' => 'decimal:3',
        'quantity_returned' => 'decimal:3',
        'expiry_date' => 'date',
        'allocation_details' => 'json',
        'picking_details' => 'json',
        'packing_details' => 'json',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that this item belongs to
     * This defines the inverse of the one-to-many relationship with Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with this order item
     * This defines the inverse of the one-to-many relationship with Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the remaining quantity to be allocated
     * This is an accessor that computes a property on the fly
     */
    public function getRemainingToAllocateAttribute(): float
    {
        return max(0, $this->quantity - $this->quantity_allocated);
    }

    /**
     * Calculate the remaining quantity to be picked
     * This is an accessor that computes a property on the fly
     */
    public function getRemainingToPickAttribute(): float
    {
        return max(0, $this->quantity - $this->quantity_picked);
    }

    /**
     * Calculate the remaining quantity to be shipped
     * This is an accessor that computes a property on the fly
     */
    public function getRemainingToShipAttribute(): float
    {
        return max(0, $this->quantity - $this->quantity_shipped);
    }

    /**
     * Calculate the allocation percentage
     * This is an accessor that computes a property on the fly
     */
    public function getAllocationPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return min(100, round(($this->quantity_allocated / $this->quantity) * 100, 2));
    }

    /**
     * Calculate the picking percentage
     * This is an accessor that computes a property on the fly
     */
    public function getPickingPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return min(100, round(($this->quantity_picked / $this->quantity) * 100, 2));
    }

    /**
     * Calculate the fulfillment percentage
     * This is an accessor that computes a property on the fly
     */
    public function getFulfillmentPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return min(100, round(($this->quantity_shipped / $this->quantity) * 100, 2));
    }

    /**
     * Recalculate the financial values for this item
     * This ensures all monetary values are consistent
     */
    public function recalculateAmounts(): void
    {
        // Calculate subtotal (quantity * unit price)
        $this->subtotal = $this->quantity * $this->unit_price;
        
        // Apply discount if applicable
        if ($this->discount_percentage > 0) {
            $this->discount_amount = $this->subtotal * ($this->discount_percentage / 100);
            $this->subtotal -= $this->discount_amount;
        }
        
        // Calculate tax amount
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        
        // Calculate total (subtotal + tax)
        $this->total = $this->subtotal + $this->tax_amount;
        
        $this->save();
        
        // Update parent order totals
        if ($this->order) {
            $this->order->updateTotals();
        }
    }

    /**
     * Record a picking transaction for this item
     * This updates the picked quantity and status, and logs picking history
     * 
     * @param float $quantity Quantity picked
     * @param int|null $locationId Bin location ID where the item was picked from
     * @param string|null $lotNumber Lot number (if applicable)
     * @param int|null $userId User who performed the picking
     * @param string|null $notes Additional notes about the picking
     */
    public function recordPicking(
        float $quantity, 
        ?int $locationId = null, 
        ?string $lotNumber = null, 
        ?int $userId = null, 
        ?string $notes = null
    ): void {
        // Update the picked quantity
        $previousQuantity = $this->quantity_picked;
        $this->quantity_picked += $quantity;
        
        // Determine the new status based on picked quantity
        if ($this->quantity_picked >= $this->quantity) {
            $this->status = 'picked';
        } else if ($this->quantity_picked > 0) {
            $this->status = 'picking';
        }
        
        // Record the picking in the picking history
        $pickingRecord = [
            'date' => now()->toDateString(),
            'quantity' => $quantity,
            'previous_total' => $previousQuantity,
            'new_total' => $this->quantity_picked,
            'location_id' => $locationId,
            'lot_number' => $lotNumber,
            'user_id' => $userId,
            'notes' => $notes,
        ];
        
        // Initialize picking_details as an empty array if it's null
        if ($this->picking_details === null) {
            $this->picking_details = [];
        }
        
        // Add the new picking record to the history
        $pickingDetails = $this->picking_details;
        $pickingDetails[] = $pickingRecord;
        $this->picking_details = $pickingDetails;
        
        $this->save();
        
        // Update the parent order status
        if ($this->order) {
            $this->order->updateStatus();
        }
    }

    /**
     * Record a packing transaction for this item
     * This updates the packing status and logs packing history
     * 
     * @param string|null $containerType Type of packaging (box, bag, etc.)
     * @param string|null $containerIdentifier Identifier for the container
     * @param int|null $userId User who performed the packing
     * @param string|null $notes Additional notes about the packing
     */
    public function recordPacking(
        ?string $containerType = null, 
        ?string $containerIdentifier = null, 
        ?int $userId = null, 
        ?string $notes = null
    ): void {
        // Ensure the item is picked before packing
        if ($this->quantity_picked <= 0) {
            throw new \Exception('Item must be picked before it can be packed.');
        }
        
        // Update the status to packed
        $this->status = 'packed';
        
        // Record the packing in the packing history
        $packingRecord = [
            'date' => now()->toDateString(),
            'quantity' => $this->quantity_picked,  // Pack the picked quantity
            'container_type' => $containerType,
            'container_identifier' => $containerIdentifier,
            'user_id' => $userId,
            'notes' => $notes,
        ];
        
        // Initialize packing_details as an empty array if it's null
        if ($this->packing_details === null) {
            $this->packing_details = [];
        }
        
        // Add the new packing record to the history
        $packingDetails = $this->packing_details;
        $packingDetails[] = $packingRecord;
        $this->packing_details = $packingDetails;
        
        $this->save();
        
        // Update the parent order status if all items are packed
        if ($this->order) {
            $orderItems = $this->order->items;
            $allPacked = true;
            
            foreach ($orderItems as $item) {
                if ($item->status !== 'packed' && $item->status !== 'shipped') {
                    $allPacked = false;
                    break;
                }
            }
            
            if ($allPacked) {
                $this->order->transitionTo('packed');
            }
        }
    }

    /**
     * Record a shipping transaction for this item
     * This updates the shipped quantity and status
     * 
     * @param float $quantity Quantity shipped
     * @param string|null $trackingNumber Tracking number for the shipment
     * @param string|null $carrier Shipping carrier
     * @param int|null $userId User who performed the shipping
     * @param string|null $notes Additional notes about the shipping
     */
    public function recordShipping(
        float $quantity, 
        ?string $trackingNumber = null, 
        ?string $carrier = null, 
        ?int $userId = null, 
        ?string $notes = null
    ): void {
        // Ensure the item is packed before shipping
        if ($this->status !== 'packed') {
            throw new \Exception('Item must be packed before it can be shipped.');
        }
        
        // Update the shipped quantity
        $previousQuantity = $this->quantity_shipped;
        $this->quantity_shipped += $quantity;
        
        // Update the status to shipped
        $this->status = 'shipped';
        
        // Record the shipping in the packing details (we'll use the same JSON field)
        $shippingRecord = [
            'type' => 'shipping',
            'date' => now()->toDateString(),
            'quantity' => $quantity,
            'previous_total' => $previousQuantity,
            'new_total' => $this->quantity_shipped,
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'user_id' => $userId,
            'notes' => $notes,
        ];
        
        // Initialize packing_details as an empty array if it's null
        if ($this->packing_details === null) {
            $this->packing_details = [];
        }
        
        // Add the shipping record to the packing history
        $packingDetails = $this->packing_details;
        $packingDetails[] = $shippingRecord;
        $this->packing_details = $packingDetails;
        
        $this->save();
        
        // Update the parent order status
        if ($this->order) {
            // If all items are shipped, update the order status
            $allShipped = true;
            foreach ($this->order->items as $item) {
                if ($item->status !== 'shipped') {
                    $allShipped = false;
                    break;
                }
            }
            
            if ($allShipped) {
                $this->order->transitionTo('shipped');
                
                // Update the order's tracking information if provided
                if ($trackingNumber && $carrier) {
                    $this->order->tracking_number = $trackingNumber;
                    $this->order->carrier = $carrier;
                    $this->order->save();
                }
            }
        }
    }

    /**
     * Cancel this order item
     * This marks the item as cancelled
     * 
     * @param int|null $userId User who performed the cancellation
     * @param string|null $reason Reason for cancellation
     */
    public function cancel(?int $userId = null, ?string $reason = null): void
    {
        // Only allow cancellation if no items have been shipped
        if ($this->quantity_shipped > 0) {
            throw new \Exception('Cannot cancel an item that has already been shipped.');
        }
        
        // Update the status to cancelled
        $this->status = 'cancelled';
        
        // Record the cancellation in the metadata
        $cancellationRecord = [
            'type' => 'cancellation',
            'date' => now()->toDateString(),
            'user_id' => $userId,
            'reason' => $reason,
        ];
        
        // Initialize metadata as an empty array if it's null
        if ($this->metadata === null) {
            $this->metadata = [];
        }
        
        // Add the cancellation record to the metadata
        $metadata = $this->metadata;
        $metadata['cancellation'] = $cancellationRecord;
        $this->metadata = $metadata;
        
        $this->save();
        
        // Update the parent order status if all items are cancelled
        if ($this->order) {
            $allCancelled = true;
            foreach ($this->order->items as $item) {
                if ($item->status !== 'cancelled') {
                    $allCancelled = false;
                    break;
                }
            }
            
            if ($allCancelled) {
                $this->order->transitionTo('cancelled', $userId, $reason);
            }
        }
    }
}