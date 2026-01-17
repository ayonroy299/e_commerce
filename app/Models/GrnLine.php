<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnLine extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'product_id',
        'variation_id',
        'quantity_received',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
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
