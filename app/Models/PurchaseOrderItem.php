<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This protects against mass-assignment vulnerabilities by specifying which
     * fields can be filled through methods like create() or fill()
     */
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'line_number',
        'quantity_ordered',
        'quantity_received',
        'quantity_rejected',
        'unit_of_measure',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'subtotal',
        'total',
        'destination_location_id',
        'status',
        'supplier_product_code',
        'notes',
        'expected_delivery_date',
        'last_received_date',
        'receiving_history',
    ];

    /**
     * The attributes that should be cast.
     * This defines how attributes should be cast when retrieved from the database
     */
    protected $casts = [
        'quantity_ordered' => 'decimal:3',
        'quantity_received' => 'decimal:3',
        'quantity_rejected' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:4',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'last_received_date' => 'date',
        'receiving_history' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the purchase order that this item belongs to
     * This defines the inverse of the one-to-many relationship with PurchaseOrder
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product associated with this purchase order item
     * This defines the inverse of the one-to-many relationship with Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the destination bin location for this item
     * This defines the inverse of the one-to-many relationship with BinLocation
     */
    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(BinLocation::class, 'destination_location_id');
    }

    /**
     * Calculate the remaining quantity to be received
     * This is an accessor that computes a property on the fly
     */
    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    /**
     * Calculate the receipt completion percentage
     * This is an accessor that computes a property on the fly
     */
    public function getReceiptCompletionPercentageAttribute(): float
    {
        if ($this->quantity_ordered <= 0) {
            return 0;
        }
        
        return min(100, round(($this->quantity_received / $this->quantity_ordered) * 100, 2));
    }

    /**
     * Recalculate the financial values for this item
     * This ensures all monetary values are consistent
     */
    public function recalculateAmounts(): void
    {
        // Calculate subtotal (quantity * unit price)
        $this->subtotal = $this->quantity_ordered * $this->unit_price;
        
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
        
        // Update parent purchase order totals
        if ($this->purchaseOrder) {
            $this->purchaseOrder->updateTotals();
        }
    }

    /**
     * Record a receipt transaction for this item
     * This updates the receipt quantities and status, and logs receipt history
     */
    public function recordReceipt(float $quantity, string $lotNumber = null, ?int $userId = null, ?string $notes = null): void
    {
        // Update the received quantity
        $previousQuantity = $this->quantity_received;
        $this->quantity_received += $quantity;
        $this->last_received_date = Carbon::today();
        
        // Determine the new status based on received quantity
        if ($this->quantity_received >= $this->quantity_ordered) {
            $this->status = $this->quantity_received > $this->quantity_ordered ? 'over_received' : 'fully_received';
        } else if ($this->quantity_received > 0) {
            $this->status = 'partially_received';
        }
        
        // Record the receipt in the receiving history
        $receiptRecord = [
            'date' => Carbon::today()->toDateString(),
            'quantity' => $quantity,
            'previous_total' => $previousQuantity,
            'new_total' => $this->quantity_received,
            'user_id' => $userId,
            'lot_number' => $lotNumber,
            'notes' => $notes,
        ];
        
        // Initialize receiving_history as an empty array if it's null
        if ($this->receiving_history === null) {
            $this->receiving_history = [];
        }
        
        // Add the new receipt record to the history
        $history = $this->receiving_history;
        $history[] = $receiptRecord;
        $this->receiving_history = $history;
        
        $this->save();
        
        // Update the parent purchase order status
        if ($this->purchaseOrder) {
            $this->purchaseOrder->updateStatus();
        }
    }

    /**
     * Record a rejection transaction for this item
     * This updates the rejected quantities and logs rejection history
     */
    public function recordRejection(float $quantity, string $reason, ?int $userId = null): void
    {
        // Update the rejected quantity
        $previousRejected = $this->quantity_rejected;
        $this->quantity_rejected += $quantity;
        
        // Record the rejection in the receiving history
        $rejectionRecord = [
            'date' => Carbon::today()->toDateString(),
            'type' => 'rejection',
            'quantity' => $quantity,
            'previous_total' => $previousRejected,
            'new_total' => $this->quantity_rejected,
            'user_id' => $userId,
            'reason' => $reason,
        ];
        
        // Initialize receiving_history as an empty array if it's null
        if ($this->receiving_history === null) {
            $this->receiving_history = [];
        }
        
        // Add the new rejection record to the history
        $history = $this->receiving_history;
        $history[] = $rejectionRecord;
        $this->receiving_history = $history;
        
        $this->save();
    }

    /**
     * Cancel this purchase order item
     * This marks the item as cancelled and updates the status
     */
    public function cancel(?int $userId = null, ?string $reason = null): void
    {
        // Only allow cancellation if no items have been received
        if ($this->quantity_received > 0) {
            throw new \Exception('Cannot cancel an item that has already been partially or fully received.');
        }
        
        $this->status = 'cancelled';
        
        // Record the cancellation in the receiving history
        $cancellationRecord = [
            'date' => Carbon::today()->toDateString(),
            'type' => 'cancellation',
            'user_id' => $userId,
            'reason' => $reason,
        ];
        
        // Initialize receiving_history as an empty array if it's null
        if ($this->receiving_history === null) {
            $this->receiving_history = [];
        }
        
        // Add the cancellation record to the history
        $history = $this->receiving_history;
        $history[] = $cancellationRecord;
        $this->receiving_history = $history;
        
        $this->save();
        
        // Update the parent purchase order status
        if ($this->purchaseOrder) {
            $this->purchaseOrder->updateStatus();
        }
    }
}