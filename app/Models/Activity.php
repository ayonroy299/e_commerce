<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Activity extends SpatieActivity
{
    use HasUlids;

    protected $keyType = 'string';
    public $incrementing = false;
}
