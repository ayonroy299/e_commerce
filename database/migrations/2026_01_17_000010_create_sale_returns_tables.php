<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('sale_id')->index();
            $table->ulid('customer_id')->nullable()->index();
            
            $table->string('reason')->nullable(); // damaged, unwanted, wrong_item
            
            $table->string('status')->default('pending')->index(); // pending, approved, rejected
            $table->string('refund_status')->default('pending')->index(); // pending, refunded, partial
            
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->ulid('created_by')->nullable();
            $table->ulid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_return_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('sale_return_id')->index();
            $table->ulid('sale_line_id')->index(); // Link to original line
            
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            
            $table->decimal('quantity_returned', 12, 2);
            $table->decimal('refund_amount', 12, 2); // Calculated value based on orig unit price
            
            $table->timestamps();
            
            $table->foreign('sale_return_id')
                  ->references('id')
                  ->on('sale_returns')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_lines');
        Schema::dropIfExists('sale_returns');
    }
};
