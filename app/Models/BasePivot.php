<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class BasePivot extends Pivot
{
    use HasUlids;

    /**
     * The value indicating whether the IDs are incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The auto-incrementing key type.
     *
     * @var string
     */
    protected $keyType = 'string';
}
