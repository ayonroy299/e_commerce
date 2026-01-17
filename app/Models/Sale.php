<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'user_id',
        'pos_session_id',
        'status',
        'payment_status',
        'invoice_number',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'paid_amount',
        'change_amount',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }
    
    // Alias for items, standardizing
    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
