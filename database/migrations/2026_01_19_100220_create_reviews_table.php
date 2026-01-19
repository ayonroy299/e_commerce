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
        Schema::create('reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->string('customer_name')->index();
            $table->tinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->boolean('is_published')->default(true);
            $table->string('source')->nullable()->index(); // e.g. landing_page
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
