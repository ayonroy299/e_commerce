<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Settings Table
        Schema::table('settings', function (Blueprint $table) {
             if (!Schema::hasColumn('settings', 'branch_id')) {
                $table->ulid('branch_id')->nullable()->after('id')->index();
             }
             // Ensure key is not unique globally if scoped by branch, but for now we keep global + branch overrides
             // Or better: Drop unique index if it exists and make it unique(branch_id, key)
             $table->dropUnique(['key']);
             $table->unique(['branch_id', 'key']); 
        });

        // 2. Update Taxes Table
        Schema::table('taxes', function (Blueprint $table) {
             if (!Schema::hasColumn('taxes', 'branch_id')) {
                $table->ulid('branch_id')->nullable()->after('id')->index();
             }
        });

        // 3. Create Currencies Table
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->ulid('branch_id')->nullable()->index();
                $table->string('name');
                $table->string('code')->unique(); // USD, BDT
                $table->string('symbol');
                $table->decimal('exchange_rate', 12, 4)->default(1.0000);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('branch_id');
            $table->dropUnique(['branch_id', 'key']);
            $table->unique('key');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        Schema::dropIfExists('currencies');
    }
};
