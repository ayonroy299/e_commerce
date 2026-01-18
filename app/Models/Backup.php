<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends BaseModel
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'user_id',
        'name',
        'file_path',
        'disk',
        'size',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
