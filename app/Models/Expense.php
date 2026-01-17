<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Expense extends BaseModel implements HasMedia
{
    use HasFactory, SoftDeletes, BelongsToBranch, InteractsWithMedia;

    protected $fillable = [
        'branch_id',
        'title',
        'date',
        'amount',
        'details',
        'status',
        'expense_category_id',
        'user_id',
        'warehouse_id',
        'attachment', // Legacy field, usage of MediaLibrary preferred
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
