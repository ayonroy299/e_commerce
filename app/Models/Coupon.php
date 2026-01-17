<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'valid_from',
        'valid_to',
        'type',
        'amount',
        'minimum_order',
        'use_limit',
    ];
}
