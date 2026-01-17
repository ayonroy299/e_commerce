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
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('customer_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUlid('variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->string('serial_no')->nullable()->index();
            $table->string('ticket_no')->unique();
            $table->text('issue');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('status', ['open', 'diagnosing', 'waiting_parts', 'repaired', 'delivered', 'closed'])->default('open');
            $table->foreignUlid('assigned_to')->nullable()->constrained('users');
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('service_actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('service_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('technician_id')->constrained('users');
            $table->text('notes');
            $table->text('internal_notes')->nullable();
            $table->decimal('cost_estimate', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_actions');
        Schema::dropIfExists('service_tickets');
    }
};
