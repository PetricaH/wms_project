<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * This protects against mass-assignment vulnerabilities by specifying which
     * fields can be filled through methods like create() or fill()
     */
    protected $fillable = [
        'order_number',
        'external_order_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'billing_address',
        'warehouse_id',
        'order_date',
        'due_date',
        'currency',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'shipping_method',
        'tracking_number',
        'carrier',
        'shipped_date',
        'estimated_delivery_date',
        'actual_delivery_date',
        'assigned_to',
        'customer_reference',
        'customer_notes',
        'internal_notes',
        'source',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     * This defines how attributes should be cast when retrieved from the database
     */
    protected $casts = [
        'order_date' => 'date',
        'due_date' => 'date',
        'shipped_date' => 'date',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'picked_at' => 'datetime',
        'packed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the warehouse associated with this order
     * This defines the inverse of the one-to-many relationship with Warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user assigned to process this order
     * This defines the inverse of the one-to-many relationship with User
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who picked this order
     * This defines the inverse of the one-to-many relationship with User
     */
    public function pickedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_by');
    }

    /**
     * Get the user who packed this order
     * This defines the inverse of the one-to-many relationship with User
     */
    public function packedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    /**
     * Get the user who cancelled this order
     * This defines the inverse of the one-to-many relationship with User
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get all items in this order
     * This defines a one-to-many relationship with OrderItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all inventory movements associated with this order
     * This defines a polymorphic one-to-many relationship with InventoryMovement
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', 'App\\Models\\Order');
    }

    /**
     * Check if the order is fully allocated
     * This is an accessor that computes a boolean property on the fly
     */
    public function getIsFullyAllocatedAttribute(): bool
    {
        foreach ($this->items as $item) {
            if ($item->quantity_allocated < $item->quantity) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if the order is fully picked
     * This is an accessor that computes a boolean property on the fly
     */
    public function getIsFullyPickedAttribute(): bool
    {
        foreach ($this->items as $item) {
            if ($item->quantity_picked < $item->quantity) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if the order is fully shipped
     * This is an accessor that computes a boolean property on the fly
     */
    public function getIsFullyShippedAttribute(): bool
    {
        foreach ($this->items as $item) {
            if ($item->quantity_shipped < $item->quantity) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Calculate the order fulfillment percentage
     * This is an accessor that computes a property on the fly
     */
    public function getFulfillmentPercentageAttribute(): float
    {
        $items = $this->items;
        
        if ($items->isEmpty()) {
            return 0;
        }
        
        $totalOrdered = $items->sum('quantity');
        $totalShipped = $items->sum('quantity_shipped');
        
        if ($totalOrdered <= 0) {
            return 0;
        }
        
        return round(($totalShipped / $totalOrdered) * 100, 2);
    }

    /**
     * Calculate the number of days until the due date
     * This is an accessor that computes a property on the fly
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return Carbon::today()->diffInDays($this->due_date, false);
    }

    /**
     * Determine if the order is overdue
     * This is an accessor that computes a boolean property on the fly
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        
        return Carbon::today()->gt($this->due_date) && !in_array($this->status, ['shipped', 'delivered', 'completed', 'cancelled', 'returned']);
    }

    /**
     * Scope a query to only include orders with a specific status
     * This allows easy filtering: Order::withStatus('processing')->get()
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include orders with a specific payment status
     * This allows easy filtering: Order::withPaymentStatus('paid')->get()
     */
    public function scopeWithPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope a query to only include orders for a specific warehouse
     * This allows easy filtering: Order::forWarehouse($warehouseId)->get()
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to include orders that are overdue
     * This helps identify orders that need attention
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->whereNotIn('status', ['shipped', 'delivered', 'completed', 'cancelled', 'returned']);
    }

    /**
     * Scope a query to include orders due within N days
     * This helps with planning
     */
    public function scopeDueWithinDays($query, $days = 7)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);
        
        return $query->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereNotIn('status', ['shipped', 'delivered', 'completed', 'cancelled', 'returned']);
    }

    /**
     * Scope a query to include orders assigned to a specific user
     * This allows easy filtering: Order::assignedTo($userId)->get()
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Update the totals on this order based on its items
     * This recalculates financial totals whenever items change
     */
    public function updateTotals(): void
    {
        $items = $this->items;
        
        $this->subtotal = $items->sum('subtotal');
        $this->tax_amount = $items->sum('tax_amount');
        
        // Calculate the total amount
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
        
        $this->save();
    }

    /**
     * Update the status of this order based on its items
     * This keeps the order status in sync with its items' statuses
     */
    public function updateStatus(): void
    {
        // If explicitly cancelled, don't change the status
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return;
        }
        
        $items = $this->items;
        
        if ($items->isEmpty()) {
            $this->status = 'pending';
        } else if ($this->isFullyShippedAttribute) {
            $this->status = 'shipped';
            $this->shipped_date = $this->shipped_date ?? Carbon::today();
        } else if ($this->isFullyPickedAttribute) {
            $this->status = 'picked';
        } else if ($items->where('status', 'picking')->count() > 0) {
            $this->status = 'picking';
        } else if ($this->isFullyAllocatedAttribute) {
            $this->status = 'ready_to_pick';
        } else if ($items->where('status', 'allocated')->count() > 0 || 
                   $items->where('status', 'partially_allocated')->count() > 0) {
            $this->status = 'processing';
        }
        
        $this->save();
    }

    /**
     * Transition the order to a new status
     * This handles status transitions and associated timestamps/user tracking
     */
    public function transitionTo(string $newStatus, ?int $userId = null, ?string $reason = null): bool
    {
        $allowedTransitions = [
            'pending' => ['processing', 'awaiting_payment', 'on_hold', 'cancelled'],
            'awaiting_payment' => ['paid', 'cancelled', 'on_hold'],
            'paid' => ['processing', 'cancelled', 'on_hold'],
            'processing' => ['ready_to_pick', 'on_hold', 'cancelled'],
            'ready_to_pick' => ['picking', 'on_hold', 'cancelled'],
            'picking' => ['picked', 'on_hold', 'cancelled'],
            'picked' => ['packing', 'on_hold', 'cancelled'],
            'packing' => ['packed', 'on_hold', 'cancelled'],
            'packed' => ['awaiting_shipment', 'on_hold', 'cancelled'],
            'awaiting_shipment' => ['shipped', 'on_hold', 'cancelled'],
            'shipped' => ['delivered', 'returned'],
            'delivered' => ['completed', 'returned'],
            'returned' => ['completed'],
            'on_hold' => ['pending', 'processing', 'ready_to_pick', 'picking', 'picked', 'packing', 'packed', 'awaiting_shipment', 'cancelled'],
            'cancelled' => [],
            'completed' => []
        ];
        
        // Check if the transition is allowed
        if (!in_array($newStatus, $allowedTransitions[$this->status] ?? [])) {
            return false;
        }
        
        $this->status = $newStatus;
        
        // Update relevant timestamps and user IDs based on the new status
        switch ($newStatus) {
            case 'picking':
                // No timestamp update here, since it's just starting to pick
                break;
                
            case 'picked':
                $this->picked_at = now();
                $this->picked_by = $userId;
                break;
                
            case 'packed':
                $this->packed_at = now();
                $this->packed_by = $userId;
                break;
                
            case 'shipped':
                $this->shipped_date = Carbon::today();
                break;
                
            case 'cancelled':
                $this->cancelled_at = now();
                $this->cancelled_by = $userId;
                $this->cancellation_reason = $reason;
                break;
                
            case 'delivered':
                $this->actual_delivery_date = Carbon::today();
                break;
        }
        
        return $this->save();
    }

    /**
     * Generate a unique order number
     * This creates sequential order numbers with a prefix
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        $year = date('y');
        $month = date('m');
        
        // Get the latest order number for this month
        $latestOrder = self::where('order_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('order_number', 'desc')
            ->first();
        
        if ($latestOrder) {
            // Extract the sequence number and increment it
            $sequence = (int) substr($latestOrder->order_number, 7);
            $sequence++;
        } else {
            // Start with sequence 1
            $sequence = 1;
        }
        
        // Format the number with leading zeros (4 digits)
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Handle inventory allocation for this order
     * This attempts to allocate inventory for all items in the order
     */
    public function allocateInventory(): array
    {
        // Track allocation results
        $results = [
            'fully_allocated' => true,
            'allocated_items' => [],
            'backordered_items' => [],
        ];
        
        // Loop through each item and attempt to allocate inventory
        foreach ($this->items as $item) {
            // Skip items that are already fully allocated
            if ($item->quantity_allocated >= $item->quantity) {
                $results['allocated_items'][] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'quantity_allocated' => $item->quantity_allocated,
                    'already_allocated' => true,
                ];
                continue;
            }
            
            // Get the remaining quantity to allocate
            $quantityToAllocate = $item->quantity - $item->quantity_allocated;
            
            // Find available inventory for this product
            $availableInventory = Inventory::where('product_id', $item->product_id)
                ->where('available_quantity', '>', 0)
                ->orderBy('received_date')  // FIFO order
                ->get();
            
            // If no inventory is available, mark as backordered
            if ($availableInventory->isEmpty()) {
                $results['fully_allocated'] = false;
                $results['backordered_items'][] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'quantity_needed' => $quantityToAllocate,
                    'reason' => 'No inventory available',
                ];
                
                // Update item status to backordered
                $item->status = 'backordered';
                $item->save();
                continue;
            }
            
            // Track how much we've been able to allocate
            $totalAllocated = 0;
            $allocationDetails = [];
            
            // Try to allocate from available inventory
            foreach ($availableInventory as $inventory) {
                $allocateFromThisLocation = min($quantityToAllocate - $totalAllocated, $inventory->available_quantity);
                
                if ($allocateFromThisLocation <= 0) {
                    continue;
                }
                
                // Record this allocation
                $allocationDetails[] = [
                    'inventory_id' => $inventory->id,
                    'location_id' => $inventory->location_id,
                    'bin_location' => $inventory->binLocation->name,
                    'quantity' => $allocateFromThisLocation,
                    'lot_number' => $inventory->lot_number,
                    'allocated_at' => now()->toDateTimeString(),
                ];
                
                // Track how much we've allocated
                $totalAllocated += $allocateFromThisLocation;
                
                // If we've allocated all we need, break out of the loop
                if ($totalAllocated >= $quantityToAllocate) {
                    break;
                }
            }
            
            // Update the item with allocation information
            $item->quantity_allocated += $totalAllocated;
            $item->allocation_details = $allocationDetails;
            
            // Update the item status based on allocation
            if ($item->quantity_allocated >= $item->quantity) {
                $item->status = 'allocated';
            } else if ($item->quantity_allocated > 0) {
                $item->status = 'partially_allocated';
                $results['fully_allocated'] = false;
                
                // Record the backordered amount
                $results['backordered_items'][] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'quantity_allocated' => $item->quantity_allocated,
                    'quantity_needed' => $item->quantity - $item->quantity_allocated,
                    'reason' => 'Insufficient inventory',
                ];
            } else {
                $item->status = 'backordered';
                $results['fully_allocated'] = false;
                
                // Record the backordered amount
                $results['backordered_items'][] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'quantity_allocated' => 0,
                    'quantity_needed' => $item->quantity,
                    'reason' => 'Insufficient inventory',
                ];
            }
            
            $item->save();
            
            // Record allocation results
            $results['allocated_items'][] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'quantity_allocated' => $item->quantity_allocated,
                'allocation_details' => $allocationDetails,
            ];
        }
        
        // Update the order status based on allocation results
        if ($results['fully_allocated']) {
            $this->transitionTo('ready_to_pick');
        } else if (count($results['allocated_items']) > 0) {
            $this->transitionTo('processing');
        }
        
        return $results;
    }
}