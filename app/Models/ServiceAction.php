<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceAction extends BaseModel
{
    protected $fillable = [
        'service_ticket_id',
        'technician_id',
        'notes',
        'internal_notes',
        'cost_estimate',
    ];

    protected $casts = [
        'cost_estimate' => 'decimal:2',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ServiceTicket::class, 'service_ticket_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
