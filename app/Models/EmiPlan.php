<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmiPlan extends BaseModel
{
    use BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'tenor_months',
        'interest_rate',
        'interest_type',
        'down_payment_percentage',
        'late_fee_type',
        'late_fee_value',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'down_payment_percentage' => 'decimal:2',
        'late_fee_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
