<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_sessions')) {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('branch_id')->index();
            $table->ulid('user_id')->index();
            
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            
            $table->string('status')->default('open')->index(); // open, closed
            
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('closing_cash', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0); // Calculated from sales
            
            $table->text('notes')->nullable(); // For discrepancies
            
            $table->timestamps();
        });
        }
        
        // Add session_id to sales table
        Schema::table('sales', function (Blueprint $table) {
             $table->ulid('pos_session_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('pos_session_id');
        });
        Schema::dropIfExists('pos_sessions');
    }
};
