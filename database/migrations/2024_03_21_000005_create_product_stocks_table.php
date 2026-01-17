<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->nullable()->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('branch_id')->index();

            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('alert_quantity', 12, 2)->nullable();

            $table->timestamps();

            // 🚫 Prevent duplicate stock rows
            $table->unique(
                ['product_id', 'variation_id', 'warehouse_id'],
                'product_stock_unique'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
};
