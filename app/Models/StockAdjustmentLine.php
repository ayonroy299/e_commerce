<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentLine extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'variation_id',
        'quantity_adjusted',
    ];
}
