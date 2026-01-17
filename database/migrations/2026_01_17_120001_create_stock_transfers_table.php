<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('from_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignUlid('to_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignUlid('from_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignUlid('to_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('transfer_no')->unique();
            $table->enum('status', ['draft', 'pending', 'sent', 'received', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('product_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->decimal('quantity', 15, 2);
            $table->decimal('received_quantity', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_lines');
        Schema::dropIfExists('stock_transfers');
    }
};
