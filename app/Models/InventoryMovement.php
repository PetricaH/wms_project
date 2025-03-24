<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory;

    /**
     * Movement types.
     */
    public const TYPE_RECEIVE = 'receive';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_PICK = 'pick';
    public const TYPE_ADJUST = 'adjust';
    public const TYPE_RETURN = 'return';
    public const TYPE_SCRAP = 'scrap';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_type',
        'reference_id',
        'product_id',
        'from_location_id',
        'to_location_id',
        'quantity',
        'unit_of_measure',
        'lot_number',
        'batch_number',
        'movement_type',
        'fifo_layers',
        'reason',
        'performed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'fifo_layers' => 'array',
    ];

    /**
     * Get all available movement types.
     */
    public static function getMovementTypes(): array
    {
        return [
            self::TYPE_RECEIVE => 'Receive',
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_PICK => 'Pick',
            self::TYPE_ADJUST => 'Adjust',
            self::TYPE_RETURN => 'Return',
            self::TYPE_SCRAP => 'Scrap',
        ];
    }

    /**
     * Get the product that owns the movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source location that owns the movement.
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(BinLocation::class, 'from_location_id');
    }

    /**
     * Get the destination location that owns the movement.
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(BinLocation::class, 'to_location_id');
    }

    /**
     * Get the reference model that owns this movement (polymorphic).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the movement.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the transactions for the movement.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Scope a query by movement type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope a query by reference type and id.
     */
    public function scopeForReference($query, $referenceType, $referenceId)
    {
        return $query->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId);
    }

    /**
     * Scope a query for date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}