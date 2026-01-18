<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'branch_id',
        'variation_id',
        'quantity',
        'alert_quantity'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'alert_quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->quantity <= $model->alert_quantity && $model->alert_quantity > 0) {
                app(\App\Services\NotificationHookService::class)->triggerLowStockAlert($model);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }
}

