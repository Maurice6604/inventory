<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the products table. Stock quantity is stored here and
     * updated ONLY via stock movement logic (never directly).
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete(); // Prevent orphaned products

            $table->string('name', 200);
            $table->string('sku', 100)->unique();   // Enforced at DB level
            $table->string('unit', 50);             // pcs, kg, litres, boxes, etc.
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('cost_price', 12, 2);
            $table->decimal('selling_price', 12, 2)->nullable();

            // Stock tracking
            $table->integer('quantity')->default(0)->unsigned(); // Current stock level
            $table->integer('minimum_stock_level')->default(0)->unsigned(); // Alert threshold

            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // NEVER hard-delete — preserve stock movement audit trail
            $table->timestamps();

            // Indexes for common query patterns
            $table->index(['category_id', 'is_active']);
            $table->index('quantity'); // For low-stock queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
