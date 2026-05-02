<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if demo data already exists (safe for container restarts)
        if (User::where('email', 'admin@demo.local')->exists()) {
            $this->command?->info('Demo data already exists — skipping seeder.');
            return;
        }

        // 1. Create Users
        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Demo Staff',
            'email' => 'staff@demo.local',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 2. Create Categories
        $categories = Category::factory()->count(5)->create();

        // 3. Create Products
        $products = Product::factory()->count(25)->create([
            'created_by' => $admin->id
        ]);

        // 4. Simulate Stock Movements
        $stockService = app(StockMovementService::class);

        foreach ($products as $product) {
            // Initial Stock In
            $initialQty = rand(20, 200);
            $stockService->addStock(
                product: $product,
                quantity: $initialQty,
                userId: $admin->id,
                source: 'Initial Setup Vendor',
                reference: 'PO-' . rand(1000, 9999),
                notes: 'Initial Demo Stock'
            );

            // Random Stock Out by Staff
            if (rand(1, 100) > 30) {
                $outQty = rand(5, $initialQty - 5);
                $stockService->removeStock(
                    product: $product,
                    quantity: $outQty,
                    userId: $staff->id,
                    source: 'Demo Order',
                    reference: 'ORD-' . rand(1000, 9999),
                    notes: 'Customer purchased items.'
                );
            }
        }
        // 5. Create some Dead Stock (Products older than 60 days with NO 'OUT' movements in last 60 days)
        $deadProducts = Product::factory()->count(3)->create([
            'created_by' => $admin->id,
            'created_at' => now()->subDays(65),
            'updated_at' => now()->subDays(65),
        ]);

        foreach ($deadProducts as $product) {
            // Add initial stock 65 days ago
            $movement = $stockService->addStock(
                product: $product,
                quantity: rand(15, 45),
                userId: $admin->id,
                source: 'Old Vendor Stock',
                reference: 'PO-DEAD-' . rand(100, 999),
                notes: 'Dead stock demo data'
            );
            
            // Backdate the movement to be older than 60 days
            \App\Models\StockMovement::where('id', $movement->id)->update([
                'created_at' => now()->subDays(65),
                'updated_at' => now()->subDays(65),
            ]);
        }
    }
}
