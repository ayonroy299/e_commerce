<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends BaseModel
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'user_id',
        'type',
        'file_path',
        'status',
        'errors_json',
        'total_rows',
        'processed_rows',
    ];

    protected $casts = [
        'errors_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
