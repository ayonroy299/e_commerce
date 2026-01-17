<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'mobile',
        'whatsapp_number',
        'tax_number',
        'billing_address',
        'shipping_address',
        'status',
        'opening_balance',
        'opening_balance_type',
        'credit_limit',
        'has_credit_limit',
        'customer_type',
    ];

    protected $casts = [
        'status' => 'boolean',
        'has_credit_limit' => 'boolean',
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
