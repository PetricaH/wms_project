<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;
    
    // the attributes that are mass assignable
    protected $fillable = [
        'warehouse_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    // the attributes that should be cast
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // get the warehouse that owns the zone
    public function warehouse(): BelongsTo {
        return $this->belongsTo(Warehouse::class);
    }

    // get the bin locations for the zone
    public function binLocations(): HasMany {
        return $this->hasMany(BinLocation::class);
    }

    // get all the inventory in this zone through bin locations
    public function inventory() {
        return Inventory::whereHas('binLocation', function ($query) {
            $query->where('zone_id', $this->id);
        });
    }

    // scope a query to only include active zones
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
