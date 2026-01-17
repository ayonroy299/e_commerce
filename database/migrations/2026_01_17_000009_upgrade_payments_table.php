<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // We will modify the existing 'payments' table to be more robust and polymorphic
        // If it has data, we might need a more careful approach, but assuming dev/early stage.
        
        Schema::table('payments', function (Blueprint $table) {
            // Add Branch
             if (!Schema::hasColumn('payments', 'branch_id')) {
                $table->ulid('branch_id')->nullable()->after('id')->index();
             }
             
             // Make order_id nullable as we will use polymorphism, or rename/replace it
             $table->string('order_id')->nullable()->change();
             
             // Add Polymorphic columns
             if (!Schema::hasColumn('payments', 'payable_type')) {
                 $table->nullableUlidMorphs('payable'); // adds payable_type, payable_id
             }
             
             if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('amount'); // cash, card, etc.
             }
             
             if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
             }
             
             if (!Schema::hasColumn('payments', 'details')) {
                 $table->json('details')->nullable();
             }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'payable_type', 'payable_id', 'payment_method', 'transaction_id', 'details']);
             // Reverting order_id to not nullable might fail if data exists that is null
        });
    }
};
