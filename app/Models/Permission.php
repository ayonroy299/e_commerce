<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Permission\Models\Permission as SpatieBasePermission;

class Permission extends SpatieBasePermission
{
    use HasUlids;
    
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:a',
        ];
    }
}
