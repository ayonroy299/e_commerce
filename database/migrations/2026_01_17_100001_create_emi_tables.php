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
        Schema::create('emi_plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('tenor_months');
            $table->decimal('interest_rate', 8, 2)->default(0);
            $table->enum('interest_type', ['flat', 'declining'])->default('flat');
            $table->decimal('down_payment_percentage', 8, 2)->default(0);
            $table->enum('late_fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('late_fee_value', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('emi_contracts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('sale_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('emi_plan_id')->constrained()->onDelete('cascade');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('down_payment', 15, 2);
            $table->decimal('financed_amount', 15, 2);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->date('start_date');
            $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
            $table->foreignUlid('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('emi_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('emi_contract_id')->constrained()->onDelete('cascade');
            $table->integer('installment_no');
            $table->date('due_date');
            $table->decimal('principal_due', 15, 2);
            $table->decimal('interest_due', 15, 2);
            $table->decimal('total_due', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->date('paid_at')->nullable();
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'overdue'])->default('pending');
            $table->timestamps();
        });

        Schema::create('emi_receipts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('emi_contract_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('payment_method_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_receipts');
        Schema::dropIfExists('emi_schedules');
        Schema::dropIfExists('emi_contracts');
        Schema::dropIfExists('emi_plans');
    }
};
