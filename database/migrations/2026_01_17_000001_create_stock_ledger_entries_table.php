<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledger_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            
            $table->decimal('qty_change', 12, 2);
            $table->decimal('new_qty', 12, 2);
            
            $table->string('type')->index(); // sale, purchase, adjustment, transfer, return
            
            // Polymorphic relation for source (Sale, PurchaseOrder, StockAdjustment, etc.)
            $table->nullableUlidMorphs('reference');
            
            $table->string('remarks')->nullable();
            
            $table->timestamps();
            
            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger_entries');
    }
};
