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
        $tables = [
            'brands',
            'categories',
            'taxes',
            'units',
            'base_units',
            'suppliers',
            'expense_categories',
            'currencies',
            'tags',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'branch_id')) {
                    $table->ulid('branch_id')->nullable()->after('id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'brands',
            'categories',
            'taxes',
            'units',
            'base_units',
            'suppliers',
            'expense_categories',
            'currencies',
            'tags',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'branch_id')) {
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
