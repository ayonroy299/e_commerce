<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationJob extends BaseModel
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'template_id',
        'recipient_type',
        'recipient_id',
        'payload',
        'scheduled_at',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }
}
