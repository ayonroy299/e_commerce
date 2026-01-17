<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariationAttributesTable extends Migration
{
    public function up()
    {
        Schema::create('product_variation_attributes', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('product_id')->index();
            $table->ulid('variation_id')->index();
            $table->ulid('attribute_id')->index();
            $table->ulid('attribute_value_id')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variation_attributes');
    }
}
