<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('purchase_order_id')->index();
            
            $table->string('status')->default('draft')->index(); // draft, approved
            
            $table->date('received_date');
            $table->text('notes')->nullable();
            
            $table->ulid('created_by')->nullable();
            $table->ulid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('grn_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('grn_id')->index();
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            
            $table->decimal('quantity_received', 12, 2);
            
            $table->timestamps();
            
            $table->foreign('grn_id')
                  ->references('id')
                  ->on('grns')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_lines');
        Schema::dropIfExists('grns');
    }
};
