<?php

namespace App\Models;

class ProductVariationAttribute extends BasePivot
{
    protected $table = 'product_variation_attributes';

    protected $fillable = [
        'product_id',
        'variation_id',
        'attribute_id',
        'attribute_value_id',
    ];
}
