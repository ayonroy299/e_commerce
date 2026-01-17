<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmiSchedule extends BaseModel
{
    protected $fillable = [
        'emi_contract_id',
        'installment_no',
        'due_date',
        'principal_due',
        'interest_due',
        'total_due',
        'paid_amount',
        'penalty_amount',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'principal_due' => 'decimal:2',
        'interest_due' => 'decimal:2',
        'total_due' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(EmiContract::class, 'emi_contract_id');
    }
}
