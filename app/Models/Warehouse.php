<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    // the attributes that are mass assignable
    protected $fillable = [
        'name',
        'code',
        'adress',
        'is_active',
    ];

    // the attributes that should be cast
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // get the zones for the warehouse
    public function zones(): HasMany {
        return $this->hasMany(Zone::class);
    }

    // get all bin locations for the warehouse through zones
    public function binLocations() {
        return $this->hasManyThrough(BinLocation::class, Zone::class);
    }

    // get all inventory in this warehouse through bin locations
    public function inventory() {
        return Inventory::whereHas('binLocation', function ($query) {
            $query->whereHas('zone', function ($query) {
                $query->where('warehouse_id', $this->id);
            });
        });
    }    

    // scope a query to only include active warehouses
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
