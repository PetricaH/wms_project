<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This protects against mass-assignment vulnerabilities by specifying which
     * fields can be filled through methods like create() or fill()
     */
    protected $fillable = [
        'name', 
        'code', 
        'contact_name', 
        'email', 
        'phone',
        'address_line1', 
        'address_line2', 
        'city', 
        'state', 
        'postal_code', 
        'country',
        'tax_id', 
        'website', 
        'account_number', 
        'payment_terms', 
        'currency',
        'lead_time_days', 
        'quality_rating', 
        'on_time_delivery_rate',
        'is_active', 
        'notes',
    ];

    /**
     * The attributes that should be cast.
     * This defines how attributes should be cast when retrieved from the database
     */
    protected $casts = [
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
        'quality_rating' => 'float',
        'on_time_delivery_rate' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all purchase orders associated with this supplier
     * This defines a one-to-many relationship between Supplier and PurchaseOrder
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the formatted full address
     * This is an accessor that computes a property on the fly
     */
    public function getFullAddressAttribute(): string
    {
        $addressParts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);
        
        return implode(', ', $addressParts);
    }

    /**
     * Scope a query to only include active suppliers
     * This allows you to easily filter: Supplier::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include suppliers with specific payment terms
     * This allows you to filter: Supplier::withPaymentTerms('Net 30')->get()
     */
    public function scopeWithPaymentTerms($query, $terms)
    {
        return $query->where('payment_terms', $terms);
    }

    /**
     * Scope a query to search suppliers by name or code
     * This helps with implementing search functionality
     */
    public function scopeSearch($query, $searchTerm)
    {
        if ($searchTerm) {
            return $query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('code', 'like', "%{$searchTerm}%");
            });
        }
        
        return $query;
    }

    /**
     * Update supplier performance metrics based on a received purchase order
     * This is a helper method to track supplier performance over time
     */
    public function updatePerformanceMetrics(PurchaseOrder $purchaseOrder): void
    {
        // This is a simplified example - in a real implementation, you'd
        // calculate this based on historical data across multiple orders
        
        // Example: Update on-time delivery rate
        if ($purchaseOrder->expected_delivery_date && $purchaseOrder->received_date) {
            $onTime = $purchaseOrder->received_date <= $purchaseOrder->expected_delivery_date;
            
            // Simple moving average calculation (you might use a more sophisticated approach)
            $currentRate = $this->on_time_delivery_rate ?? 0;
            $weight = 0.2; // Weight for the new data point (adjust as needed)
            
            $this->on_time_delivery_rate = ($currentRate * (1 - $weight)) + ($onTime ? $weight * 100 : 0);
            $this->save();
        }
        
        // Similar logic could be implemented for lead time calculations and quality ratings
    }
}