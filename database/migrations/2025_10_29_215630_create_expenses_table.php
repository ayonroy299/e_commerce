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
        Schema::create('expenses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->date('date')->nullable();
            $table->decimal('amount')->nullable();
            $table->text('details')->nullable();
            $table->boolean('status')->default(true);
            $table->ulid('expense_category_id')->index();
            $table->ulid('user_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
