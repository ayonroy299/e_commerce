<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('supplier_id')->index();
            
            $table->string('status')->default('draft')->index(); // draft, ordered, partially_received, received, cancelled
            
            $table->date('date');
            $table->date('expected_date')->nullable();
            
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            
            $table->ulid('created_by')->nullable();
            $table->ulid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('purchase_order_id')->index();
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            
            $table->decimal('quantity_ordered', 12, 2);
            $table->decimal('quantity_received', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 12, 2);
            
            $table->timestamps();
            
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('purchase_orders')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
    }
};
