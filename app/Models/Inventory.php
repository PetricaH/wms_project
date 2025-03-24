<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\BelongsToRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionFunctionAbstract;

class Inventory extends Model
{
    use HasFactory;

    // the table associated with the model
    protected $table = 'inventory';

    // the attributes that are mass assignable
    protected $fillable = [
        'product_id',
        'location_id',
        'lot_number',
        'batch_number',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'unit_of_measure',
        'expiry_date',
        'received_date',
    ];

    // the attributes that should be cast
    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];

    // the booted method of the model
    protected static function booted() {
        static::saving(function ($inventory) {
            // auto-calculate available quantity
            $inventory->available_quantity = $inventory->quantity - $inventory->reserved_quantity;
        });
    }

    // get the product that owns the inventory
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    // get the bin location taht owns the inventory
    public function binLocation(): BelongsTo {
        return $this->belongsTo(BinLocation::class, 'location_id');
    }

    // scope a query to only include inventory with stock
    public function scopeInStock($query) {
        return $query->where('quantity', '>', 0);
    }

    // scope a query to only include inventory with available stock
    public function scopeAvailable($query) {
        return $query->where('available_quantity', '>', 0);
    }

    // scope a query to only include inventory by expiry date
    public function scopeExpiresBefore($query, $date) {
        return $query->where('expiry_date', '<=', $date);
    }

    // scope a query to only include inventory that is not expired
    public function scopeNotExpired($query) {
        return $query->where(function ($query){
            $query->whereNull('expiry_date')
                ->orWhere('expiry_date', '>', now());
        });
    }

    // check if inventory is expired
    public function isExpired() {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // get the days until expiry
    public function getDaysUntilExpiryAttribute() {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }
}