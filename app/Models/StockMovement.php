<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * Movement type constants — use these everywhere to avoid magic strings.
     */
    const TYPE_IN         = 'IN';
    const TYPE_OUT        = 'OUT';
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    /**
     * Mass-assignable attributes.
     * 'quantity_before' and 'quantity_after' are set programmatically
     * by StockMovementService — they are intentionally included so the
     * service can mass-assign a complete snapshot in one call.
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference',
        'source',
        'notes',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The product this movement belongs to.
     * withTrashed() ensures soft-deleted products are still accessible
     * when browsing the historical audit trail.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * The user who performed this movement.
     * withTrashed() protects the audit trail if a user is ever removed.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Filter by movement type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Only IN movements.
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', self::TYPE_IN);
    }

    /**
     * Only OUT movements.
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /**
     * Only ADJUSTMENT movements.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }

    /**
     * Movements within a date range.
     */
    public function scopeBetweenDates($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [
            $from . ' 00:00:00',
            $to   . ' 23:59:59',
        ]);
    }

    /**
     * Movements for a specific product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    // -------------------------------------------------------------------------
    // Helpers / Accessors
    // -------------------------------------------------------------------------

    /**
     * Human-readable label for the movement type.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_IN         => 'Stock In',
            self::TYPE_OUT        => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default               => 'Unknown',
        };
    }

    /**
     * CSS badge class for displaying type in the UI.
     */
    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            self::TYPE_IN         => 'success',
            self::TYPE_OUT        => 'danger',
            self::TYPE_ADJUSTMENT => 'warning',
            default               => 'secondary',
        };
    }

    /**
     * Whether this movement increased or decreased stock.
     * Useful for display: +50 vs -30.
     */
    public function signedQuantity(): string
    {
        return match ($this->type) {
            self::TYPE_IN  => "+{$this->quantity}",
            self::TYPE_OUT => "-{$this->quantity}",
            default        => ($this->quantity >= 0 ? "+{$this->quantity}" : "{$this->quantity}"),
        };
    }
}
