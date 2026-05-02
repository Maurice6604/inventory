<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     * NOTE: 'quantity' is intentionally EXCLUDED from $fillable.
     * Stock must only be updated via StockMovementService to preserve
     * the audit trail and enforce transaction safety.
     */
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'unit',
        'description',
        'cost_price',
        'selling_price',
        'minimum_stock_level',
        'is_active',
        'created_by',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'cost_price'          => 'decimal:2',
        'selling_price'       => 'decimal:2',
        'quantity'            => 'integer',
        'minimum_stock_level' => 'integer',
        'is_active'           => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The category this product belongs to.
     * withTrashed() ensures we can still retrieve the category name
     * even if the category was soft-deleted.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * The user who created this product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All stock movements for this product (the audit trail).
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    /**
     * Most recent stock movement.
     */
    public function latestMovement(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at')->limit(1);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Products where current stock is at or below minimum_stock_level.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'minimum_stock_level')
                     ->where('is_active', true);
    }

    /**
     * Only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Search by name or SKU.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by category.
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // -------------------------------------------------------------------------
    // Computed Attributes / Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns true when current stock is at or below the minimum level.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock_level;
    }

    /**
     * Returns true when stock is completely out.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * How many units below the minimum threshold (0 if above threshold).
     */
    public function stockDeficit(): int
    {
        return max(0, $this->minimum_stock_level - $this->quantity);
    }

    /**
     * Human-readable stock status label.
     */
    public function stockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }

        if ($this->isLowStock()) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    /**
     * CSS class suffix for stock-status badges in the UI.
     */
    public function stockStatusClass(): string
    {
        return match (true) {
            $this->isOutOfStock() => 'danger',
            $this->isLowStock()   => 'warning',
            default               => 'success',
        };
    }
}
