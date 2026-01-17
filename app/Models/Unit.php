<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\BelongsToBranch;

class Unit extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'short_code',
        'status',
        'base_unit_id',
        'branch_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => 'boolean',
            'base_unit_id' => 'string',
        ];
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(BaseUnit::class);
    }
}
