<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            $table->ulid('branch_id')->index();
            
            $table->ulid('from_warehouse_id')->nullable()->index();
            $table->ulid('to_warehouse_id')->nullable()->index();
            
            $table->enum('type', ['in', 'out', 'transfer', 'adjust']);
            $table->decimal('quantity', 12, 2);

            $table->string('reference')->nullable();  // invoice, PO, note
            $table->text('note')->nullable();

            $table->ulid('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['product_id', 'variation_id', 'type']);
            
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
