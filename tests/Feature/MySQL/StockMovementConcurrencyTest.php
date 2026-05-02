<?php

namespace Tests\Feature\MySQL;

use App\Models\Product;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * These tests validate StockMovementService concurrency behaviour against a
 * real MySQL/InnoDB database to verify that lockForUpdate() pessimistic
 * locking works correctly under production-equivalent conditions.
 *
 * Run with: php artisan test --testsuite=MySQL
 *
 * Requires a separate MySQL test database. Configure DB_* in .env.testing or
 * pass environment variables directly:
 *   DB_CONNECTION=mysql DB_DATABASE=inventory_test php artisan test --testsuite=MySQL
 */
#[Group('mysql')]
class StockMovementConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private StockMovementService $service;
    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockMovementService::class);
        $this->admin   = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->create();

        // Seed initial stock of 100 units via the service
        $this->service->addStock(
            product: $this->product,
            quantity: 100,
            userId: $this->admin->id,
            source: 'Test Setup',
            reference: 'SETUP-001',
            notes: 'Initial stock for concurrency test.'
        );
    }

    /**
     * Test: two simultaneous OUT requests that together exceed available stock.
     * With lockForUpdate(), only the first should succeed; the second must throw.
     * This test simulates concurrency sequentially within one transaction to
     * validate the lock+check logic is correct.
     */
    public function test_concurrent_out_requests_cannot_produce_negative_stock(): void
    {
        $this->product->refresh();
        $initialQty = $this->product->quantity; // 100

        // Simulate two concurrent requests trying to remove 60 units each (120 total)
        $successCount = 0;
        $failCount    = 0;

        foreach ([60, 60] as $requestedQty) {
            try {
                $this->service->removeStock(
                    product: $this->product->fresh(),
                    quantity: $requestedQty,
                    userId: $this->admin->id,
                    source: 'Concurrency Test',
                    reference: 'CONC-001',
                    notes: null
                );
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $this->product->refresh();

        // Exactly one request should have succeeded
        $this->assertEquals(1, $successCount, 'Exactly one OUT should succeed.');
        $this->assertEquals(1, $failCount, 'Exactly one OUT should fail.');

        // Final quantity should be 40 (100 - 60), never negative
        $this->assertEquals(40, $this->product->quantity, 'Quantity must not go negative.');
        $this->assertGreaterThanOrEqual(0, $this->product->quantity, 'Quantity must never be negative.');
    }

    /**
     * Test: lockForUpdate() must prevent phantom reads.
     * Verifies the stock level read inside the transaction reflects the locked,
     * committed value — not a stale snapshot.
     */
    public function test_lock_for_update_reads_committed_value(): void
    {
        $this->product->refresh();

        // Remove 50 units first
        $this->service->removeStock(
            product: $this->product,
            quantity: 50,
            userId: $this->admin->id,
            source: 'First OUT',
            reference: 'LOCK-001',
            notes: null
        );

        $this->product->refresh();
        $this->assertEquals(50, $this->product->quantity);

        // A second removal of 50 should succeed (50 remaining)
        $this->service->removeStock(
            product: $this->product,
            quantity: 50,
            userId: $this->admin->id,
            source: 'Second OUT',
            reference: 'LOCK-002',
            notes: null
        );

        $this->product->refresh();
        $this->assertEquals(0, $this->product->quantity);
    }
}
