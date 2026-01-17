<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('customer_id')->nullable()->index(); // Nullable for generic walk-in
            $table->ulid('user_id')->index(); // Cashier/Salesperson
            
            $table->string('status')->default('completed')->index(); // completed, returned, draft, void
            $table->string('payment_status')->default('paid')->index(); // paid, partial, pending
            
            $table->string('invoice_number')->unique();
            
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change_amount', 12, 2)->default(0); // Change given back
            
            $table->text('notes')->nullable();
            
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('sale_id')->index();
            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('sale_id')
                  ->references('id')
                  ->on('sales')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_lines');
        Schema::dropIfExists('sales');
    }
};
