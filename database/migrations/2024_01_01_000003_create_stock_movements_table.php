<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The stock_movements table is the IMMUTABLE audit trail.
     * Every stock change (IN, OUT, ADJUSTMENT) records here first.
     * Records are NEVER deleted — they are the source of truth.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // References the product (soft-deleted products retained via unsignedBigInteger)
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->restrictOnDelete(); // Prevent accidental product hard-deletes

            // The user who performed the action
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->restrictOnDelete();

            // Movement type
            $table->enum('type', ['IN', 'OUT', 'ADJUSTMENT']);

            // For ADJUSTMENT: this can be negative (stock correction downward)
            // For IN/OUT: always positive (direction conveyed by type)
            $table->integer('quantity');

            // Stock snapshot for auditability
            $table->integer('quantity_before'); // Stock level BEFORE this movement
            $table->integer('quantity_after');  // Stock level AFTER this movement

            // Source / reason
            $table->string('reference', 200)->nullable(); // e.g. PO-001, REQ-042
            $table->string('source', 100)->nullable();    // purchase, restock, sale, return, etc.
            $table->text('notes')->nullable();

            // No softDeletes — movement records are permanent
            $table->timestamps();

            // Indexes for filtering/reporting queries
            $table->index(['product_id', 'type']);
            $table->index(['user_id']);
            $table->index('created_at'); // Date-range filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
