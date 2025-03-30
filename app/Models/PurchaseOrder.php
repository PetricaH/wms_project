<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This protects against mass-assignment vulnerabilities by specifying which
     * fields can be filled through methods like create() or fill()
     */
    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'created_by',
        'order_date',
        'expected_delivery_date',
        'received_date',
        'currency',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'status',
        'supplier_reference',
        'shipping_address',
        'notes',
        'payment_terms',
        'allows_partial_receiving',
    ];

    /**
     * The attributes that should be cast.
     * This defines how attributes should be cast when retrieved from the database
     */
    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'received_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'allows_partial_receiving' => 'boolean',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the supplier that this purchase order belongs to
     * This defines the inverse of the one-to-many relationship with Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse this purchase order is for
     * This defines the inverse of the one-to-many relationship with Warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created this purchase order
     * This defines the inverse of the one-to-many relationship with User
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this purchase order (if applicable)
     * This defines the inverse of the one-to-many relationship with User
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who sent this purchase order (if applicable)
     * This defines the inverse of the one-to-many relationship with User
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Get the user who cancelled this purchase order (if applicable)
     * This defines the inverse of the one-to-many relationship with User
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get all items in this purchase order
     * This defines a one-to-many relationship with PurchaseOrderItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get all receipt transactions associated with this purchase order
     * This defines a polymorphic one-to-many relationship with InventoryMovement
     */
    public function receiptTransactions(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', 'App\\Models\\PurchaseOrder')
            ->where('movement_type', 'receive');
    }

    /**
     * Calculate the received percentage for this purchase order
     * This is an accessor that computes a property on the fly
     */
    public function getReceivedPercentageAttribute(): float
    {
        $items = $this->items;
        
        if ($items->isEmpty()) {
            return 0;
        }
        
        $totalOrdered = $items->sum('quantity_ordered');
        $totalReceived = $items->sum('quantity_received');
        
        if ($totalOrdered <= 0) {
            return 0;
        }
        
        return round(($totalReceived / $totalOrdered) * 100, 2);
    }

    /**
     * Check if the purchase order is fully received
     * This is an accessor that computes a boolean property on the fly
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        foreach ($this->items as $item) {
            if ($item->quantity_received < $item->quantity_ordered) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Scope a query to only include purchase orders with a specific status
     * This allows easy filtering: PurchaseOrder::withStatus('sent')->get()
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include purchase orders for a specific supplier
     * This allows easy filtering: PurchaseOrder::forSupplier($supplierId)->get()
     */
    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope a query to only include purchase orders for a specific warehouse
     * This allows easy filtering: PurchaseOrder::forWarehouse($warehouseId)->get()
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to include purchase orders that are overdue for delivery
     * This helps identify orders that need follow-up
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['sent', 'confirmed', 'partially_received'])
            ->whereNotNull('expected_delivery_date')
            ->where('expected_delivery_date', '<', Carbon::today());
    }

    /**
     * Scope a query to include purchase orders due for delivery in the next N days
     * This helps with receiving planning
     */
    public function scopeDueWithinDays($query, $days = 7)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);
        
        return $query->whereIn('status', ['sent', 'confirmed', 'partially_received'])
            ->whereNotNull('expected_delivery_date')
            ->whereBetween('expected_delivery_date', [$startDate, $endDate]);
    }

    /**
     * Update the totals on this purchase order based on its items
     * This recalculates financial totals whenever items change
     */
    public function updateTotals(): void
    {
        $items = $this->items;
        
        $this->subtotal = $items->sum('subtotal');
        $this->tax_amount = $items->sum('tax_amount');
        
        // Calculate the total (subtotal + tax + shipping)
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost;
        
        $this->save();
    }

    /**
     * Update the status of this purchase order based on its items
     * This keeps the PO status in sync with its items' statuses
     */
    public function updateStatus(): void
    {
        // If explicitly cancelled, don't change the status
        if ($this->status === 'cancelled') {
            return;
        }
        
        $items = $this->items;
        
        if ($items->isEmpty()) {
            $this->status = 'draft';
        } else if ($items->where('status', 'fully_received')->count() === $items->count()) {
            $this->status = 'fully_received';
            $this->received_date = Carbon::today();
        } else if ($items->where('status', 'partially_received')->count() > 0 || 
                   $items->where('status', 'fully_received')->count() > 0) {
            $this->status = 'partially_received';
        }
        
        $this->save();
    }

    /**
     * Transition the purchase order to a new status
     * This handles status transitions and associated timestamps/user tracking
     */
    public function transitionTo(string $newStatus, ?int $userId = null, ?string $reason = null): bool
    {
        $allowedTransitions = [
            'draft' => ['awaiting_approval', 'cancelled'],
            'awaiting_approval' => ['approved', 'draft', 'cancelled'],
            'approved' => ['sent', 'draft', 'cancelled'],
            'sent' => ['confirmed', 'partially_received', 'fully_received', 'cancelled'],
            'confirmed' => ['partially_received', 'fully_received', 'cancelled'],
            'partially_received' => ['fully_received', 'closed', 'cancelled'],
            'fully_received' => ['closed'],
            'closed' => [],
            'cancelled' => []
        ];
        
        // Check if the transition is allowed
        if (!in_array($newStatus, $allowedTransitions[$this->status] ?? [])) {
            return false;
        }
        
        $this->status = $newStatus;
        
        // Update relevant timestamps and user IDs based on the new status
        switch ($newStatus) {
            case 'approved':
                $this->approved_at = now();
                $this->approved_by = $userId;
                break;
                
            case 'sent':
                $this->sent_at = now();
                $this->sent_by = $userId;
                break;
                
            case 'cancelled':
                $this->cancelled_at = now();
                $this->cancelled_by = $userId;
                $this->cancellation_reason = $reason;
                break;
                
            case 'fully_received':
                $this->received_date = now();
                break;
        }
        
        return $this->save();
    }

    /**
     * Generate a unique purchase order number
     * This creates sequential PO numbers with a prefix
     */
    public static function generatePONumber(): string
    {
        $prefix = 'PO-';
        $year = date('y');
        $month = date('m');
        
        // Get the latest PO number for this month
        $latestPO = self::where('po_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('po_number', 'desc')
            ->first();
        
        if ($latestPO) {
            // Extract the sequence number and increment it
            $sequence = (int) substr($latestPO->po_number, 7);
            $sequence++;
        } else {
            // Start with sequence 1
            $sequence = 1;
        }
        
        // Format the number with leading zeros (4 digits)
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}