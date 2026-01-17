<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableUlidMorphs('subject', 'subject');
            $table->nullableUlidMorphs('causer', 'causer');
            $table->string('event')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_log');
    }
};
