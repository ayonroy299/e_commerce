<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributeValuesTable extends Migration
{
    public function up()
    {
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('attribute_id')->index();
            $table->string('value'); // e.g., "Red", "XL", "8GB"
            $table->string('display_value')->nullable();
            $table->string('color_code')->nullable(); // For color type attributes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_attribute_values');
    }
}
