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
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('pos_session_id')->index();
            $table->ulid('branch_id')->nullable()->index();
            $table->ulid('customer_id')->nullable()->index();
            $table->ulid('user_id')->index(); // cashier

            $table->string('invoice_no', 100)->unique()->nullable();
            $table->string('reference_no', 100)->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);

            $table->string('payment_status', 50)->default('unpaid'); // unpaid, partial, paid
            $table->string('status', 50)->default('completed');      // draft, completed, void

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
