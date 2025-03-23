<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    // the attributes that are mass assignable
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'price',
        'cost',
        'upc',
        'weight',
        'dimensions',
        'attributes',
        'is_active',
    ];

    // the attributes that should be cast
    protected $casts = [
        'price' => 'decimal:2',
        'cost'  => 'decimal:2',
        'weight'=> 'decimal:3',
        'dimensions' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    // get the category that owns the product
    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    // get the inventory entries for the product
    public function inventory(): HasMany {
        return $this->hasMany(Inventory::class);
    }

    // get the inventory movements for the product
    public function inventoryMovement(): HasMany {
        return $this->hasMany(InventoryMovement::class);
    }

    // get total quantity in inventory
    public function getTotalQuantityAttribute() {
        return $this->inventory()->sum('quantity');
    }

    // get  the avaiable quantity in inventory
    public function getAvailableQuantityAttribute() {
        return $this->inventory()->sum('avaiable_quantity');
    }

    // get the reserved quantity in inventory 
    public function getReservedQuantityAttribute() {
        return $this->inventory()->sum('reserved_quantity');
    }

    // scope a query to only include active products
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
