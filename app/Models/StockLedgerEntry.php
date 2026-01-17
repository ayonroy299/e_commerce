<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockLedgerEntry extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'product_id',
        'variation_id',
        'qty_change',
        'new_qty',
        'type',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
