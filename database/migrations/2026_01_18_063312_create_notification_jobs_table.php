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
        Schema::create('notification_jobs', function (Blueprint $table) {
            $table->id();
            $table->ulid('branch_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('recipient_type'); // User, Customer
            $table->ulid('recipient_id');
            $table->json('payload')->nullable(); // Data for placeholders
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_jobs');
    }
};
