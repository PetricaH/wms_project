<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BinLocation extends Model
{
    use HasFactory;

    // the attributes that are mass assignable
    protected $fillable = [
        'zone_id',
        'name', 
        'code',
        'position',
        'capacity',
        'is_active',
    ];

    // the attributes that should be cast
    protected $casts = [
        'position' => 'array',
        'capacity' => 'decimal:3',
        'is_active'=> 'boolean',
    ];

    // get the zone that owns the bin location
    public function zone(): BelongsTo {
        return $this->belongsTo(Zone::class);
    }

    // get the warehouse through the zone
    public function warehouse() {
        return $this->zone->warehouse;
    }

    // get the inventory items stored in this bin location
    public function inventory(): HasMany {
        return $this->hasMany(Inventory::class, 'location_id');
    }

    // get formatted position display
    public function getFormattedPositionAttribute() {
        if (empty($this->position)) {
            return null;
        }

        $positon = $this->position;
        return sprintf(
            'Asile: %sm Bay: %s, Level: %s',
            $position['aisle'] ?? 'N/A',
            $positon['bay'] ?? 'N/A',
            $position['level'] ?? 'N/A'
        );
    }

    // get the full location path
    public function getFullPathAttribute() {
        return sprintf(
            '%s > %s > %s',
            $this->zone->warehouse->code,
            $this->zone->code,
            $this->code
        );
    }

    // scope a query to only include active bin locations
    public function scopeActive($query) {
        return $query->query('is_active', true);
    }
}