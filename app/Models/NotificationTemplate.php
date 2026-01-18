<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends BaseModel
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'subject',
        'body',
        'channels',
        'is_active',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(NotificationJob::class, 'template_id');
    }
}
