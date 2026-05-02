<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockMovementService $stockService;
    protected User $adminUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stockService = app(StockMovementService::class);
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        
        $category = Category::create([
            'name' => 'Test Category'
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'cost_price' => 10.00,
            'unit' => 'pcs',
            // Default quantity is 0
        ]);
    }

    public function test_can_add_stock()
    {
        $this->stockService->addStock(
            product: $this->product,
            quantity: 50,
            userId: $this->adminUser->id,
            source: 'Vendor A',
            reference: 'PO-100',
            notes: 'Initial Stock'
        );

        $this->product->refresh();
        $this->assertEquals(50, $this->product->quantity);

        // Verify audit trail
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'IN',
            'quantity_before' => 0,
            'quantity_after' => 50,
            'source' => 'Vendor A',
            'reference' => 'PO-100',
        ]);
    }

    public function test_can_remove_stock()
    {
        // First add stock
        $this->stockService->addStock(
            product: $this->product,
            quantity: 50,
            userId: $this->adminUser->id
        );

        // Then remove stock
        $this->stockService->removeStock(
            product: $this->product,
            quantity: 20,
            userId: $this->adminUser->id,
            source: 'Customer Order',
            reference: 'ORD-100'
        );

        $this->product->refresh();
        $this->assertEquals(30, $this->product->quantity);

        // Verify audit trail
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'OUT',
            'quantity_before' => 50,
            'quantity_after' => 30,
            'source' => 'Customer Order',
        ]);
    }

    public function test_cannot_remove_stock_below_zero()
    {
        // Add 10 items
        $this->stockService->addStock(
            product: $this->product,
            quantity: 10,
            userId: $this->adminUser->id
        );

        // Expect Exception when trying to remove 15
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->stockService->removeStock(
            product: $this->product,
            quantity: 15,
            userId: $this->adminUser->id
        );
    }

    public function test_can_adjust_stock()
    {
        // Add 10 items
        $this->stockService->addStock(
            product: $this->product,
            quantity: 10,
            userId: $this->adminUser->id
        );

        // Adjust to 25
        $this->stockService->adjustStock(
            product: $this->product,
            newTotalQuantity: 25,
            userId: $this->adminUser->id,
            source: 'Audit Correction',
            notes: 'Found missing box'
        );

        $this->product->refresh();
        $this->assertEquals(25, $this->product->quantity);

        // Verify audit trail for adjustment
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'ADJUSTMENT',
            'quantity_before' => 10,
            'quantity_after' => 25,
            'source' => 'Audit Correction',
        ]);
    }

    public function test_stock_quantity_attribute_is_not_mass_assignable()
    {
        // Attempt mass assignment
        $hackedProduct = Product::create([
            'category_id' => $this->product->category_id,
            'name' => 'Hacked Product',
            'sku' => 'HACK-001',
            'cost_price' => 10.00,
            'unit' => 'pcs',
            'quantity' => 1000 // Should be ignored
        ]);

        $this->assertEquals(0, $hackedProduct->quantity);
    }
}
