<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('warehouse_id')->index();
            
            $table->string('reason')->index(); // damage, loss, audit, opening_balance
            $table->string('status')->default('draft')->index(); // draft, approved
            
            $table->date('date');
            $table->text('notes')->nullable();
            
            $table->ulid('created_by')->nullable();
            $table->ulid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Lines for the adjustment
        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('stock_adjustment_id')->index();
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            
            $table->decimal('quantity_adjusted', 12, 2); // Can be positive or negative
            
            $table->timestamps();
            
            $table->foreign('stock_adjustment_id')
                  ->references('id')
                  ->on('stock_adjustments')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_lines');
        Schema::dropIfExists('stock_adjustments');
    }
};
