<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    /**
     * Transaction types.
     */
    public const TYPE_INCREMENT = 'increment';
    public const TYPE_DECREMENT = 'decrement';
    public const TYPE_ADJUST = 'adjust';
    public const TYPE_RESERVE = 'reserve';
    public const TYPE_UNRESERVE = 'unreserve';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_movement_id',
        'transaction_type',
        'product_id',
        'location_id',
        'quantity',
        'running_balance',
        'unit_cost',
        'total_cost',
        'lot_number',
        'batch_number',
        'performed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'running_balance' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get all available transaction types.
     */
    public static function getTransactionTypes(): array
    {
        return [
            self::TYPE_INCREMENT => 'Increment',
            self::TYPE_DECREMENT => 'Decrement',
            self::TYPE_ADJUST => 'Adjust',
            self::TYPE_RESERVE => 'Reserve',
            self::TYPE_UNRESERVE => 'Unreserve',
        ];
    }

    /**
     * Get the movement that owns the transaction.
     */
    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }

    /**
     * Get the product that owns the transaction.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location that owns the transaction.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(BinLocation::class, 'location_id');
    }

    /**
     * Get the user who performed the transaction.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope a query by transaction type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope a query for date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}