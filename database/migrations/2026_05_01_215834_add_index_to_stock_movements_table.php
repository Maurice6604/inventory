<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Composite index to accelerate dead stock and top movers queries in ReportController.
            $table->index(['product_id', 'type', 'created_at'], 'sm_product_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('sm_product_type_date_idx');
        });
    }
};
