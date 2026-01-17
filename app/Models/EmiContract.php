<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmiContract extends BaseModel
{
    use BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'sale_id',
        'emi_plan_id',
        'principal_amount',
        'down_payment',
        'financed_amount',
        'interest_amount',
        'total_amount',
        'start_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'financed_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'start_date' => 'date',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(EmiPlan::class, 'emi_plan_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmiSchedule::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(EmiReceipt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
