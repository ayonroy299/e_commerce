<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnLine extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'sale_return_id',
        'sale_line_id',
        'product_id',
        'variation_id',
        'quantity_returned',
        'refund_amount',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

     public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }
}
