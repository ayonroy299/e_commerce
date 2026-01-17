<?php

namespace App\Models;

class ProductTag extends BasePivot
{
    protected $table = 'product_tag';

    protected $fillable = [
        'product_id',
        'tag_id',
    ];
}
