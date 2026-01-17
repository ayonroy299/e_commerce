<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmiReceipt extends BaseModel
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'emi_contract_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(EmiContract::class, 'emi_contract_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
