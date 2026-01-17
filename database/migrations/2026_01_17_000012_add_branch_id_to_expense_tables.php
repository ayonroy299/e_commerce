<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
             if (!Schema::hasColumn('expense_categories', 'branch_id')) {
                $table->ulid('branch_id')->nullable()->after('id')->index();
             }
        });

        Schema::table('expenses', function (Blueprint $table) {
             if (!Schema::hasColumn('expenses', 'branch_id')) {
                $table->ulid('branch_id')->nullable()->after('id')->index();
             }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
        
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
