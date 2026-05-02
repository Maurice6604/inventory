<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class StockMovementService
{
    /**
     * Add stock to a product (IN).
     */
    public function addStock(Product $product, int $quantity, int $userId, string $source = null, string $reference = null, string $notes = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than zero for stock in.");
        }

        return $this->recordMovement($product, StockMovement::TYPE_IN, $quantity, $userId, $source, $reference, $notes);
    }

    /**
     * Remove stock from a product (OUT).
     */
    public function removeStock(Product $product, int $quantity, int $userId, string $source = null, string $reference = null, string $notes = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than zero for stock out.");
        }

        return $this->recordMovement($product, StockMovement::TYPE_OUT, $quantity, $userId, $source, $reference, $notes);
    }

    /**
     * Adjust stock to a specific target amount (ADJUSTMENT).
     * Calculates the difference and applies it.
     */
    public function adjustStock(Product $product, int $newTotalQuantity, int $userId, string $source = 'Manual Adjustment', string $reference = null, string $notes = null): ?StockMovement
    {
        if ($newTotalQuantity < 0) {
            throw new Exception("Stock cannot be adjusted to a negative value.");
        }

        return DB::transaction(function () use ($product, $newTotalQuantity, $userId, $source, $reference, $notes) {
            // Lock the row to ensure we read the absolute latest quantity
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->first();
            
            $difference = $newTotalQuantity - $lockedProduct->quantity;
            
            if ($difference === 0) {
                return null; // No change needed
            }

            return $this->processMovement($lockedProduct, StockMovement::TYPE_ADJUSTMENT, $difference, $userId, $source, $reference, $notes);
        });
    }

    /**
     * Internal method to safely wrap any movement in a transaction with locks.
     */
    protected function recordMovement(Product $product, string $type, int $quantity, int $userId, ?string $source, ?string $reference, ?string $notes): StockMovement
    {
        return DB::transaction(function () use ($product, $type, $quantity, $userId, $source, $reference, $notes) {
            // Re-fetch with a pessimistic lock to prevent race conditions during concurrent requests
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->first();

            if ($type === StockMovement::TYPE_OUT && $lockedProduct->quantity < $quantity) {
                throw new Exception("Insufficient stock. Available: {$lockedProduct->quantity}, Requested: {$quantity}");
            }

            // For OUT type, we subtract the quantity. For IN and ADJUSTMENT, we add.
            $signedQuantity = ($type === StockMovement::TYPE_OUT) ? -$quantity : $quantity;

            return $this->processMovement($lockedProduct, $type, $signedQuantity, $userId, $source, $reference, $notes);
        });
    }

    /**
     * Core processing method (Must be called within a DB transaction & locked Product row).
     */
    protected function processMovement(Product $lockedProduct, string $type, int $signedQuantity, int $userId, ?string $source, ?string $reference, ?string $notes): StockMovement
    {
        $quantityBefore = $lockedProduct->quantity;
        $quantityAfter = $quantityBefore + $signedQuantity;

        // Failsafe validation
        if ($quantityAfter < 0) {
            throw new Exception("System integrity error: Stock calculation resulted in negative value.");
        }

        // 1. Create the immutable audit record FIRST
        $movement = StockMovement::create([
            'product_id' => $lockedProduct->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => ($type === StockMovement::TYPE_ADJUSTMENT) ? $signedQuantity : abs($signedQuantity),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'source' => $source,
            'reference' => $reference,
            'notes' => $notes,
        ]);

        // 2. Force update the product quantity (bypassing fillable)
        $lockedProduct->forceFill([
            'quantity' => $quantityAfter
        ])->save();

        return $movement;
    }
}
