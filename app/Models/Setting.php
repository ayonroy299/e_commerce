<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = ['branch_id', 'key', 'value'];

    // Helper to get settings for current branch
    public static function getSettings($keys = [])
    {
        $branchId = auth()->user()->branch_id ?? null;
        $query = self::where('branch_id', $branchId);
        
        if (!empty($keys)) {
            $query->whereIn('key', $keys);
        }
        
        return $query->pluck('value', 'key');
    }

    /**
     * Get a setting value by key.
     * Prioritizes branch-specific setting if user is logged in, otherwise global.
     */
    public static function get($key, $default = null)
    {
        // 1. Try Branch Specific (if auth)
        if ($user = auth()->user()) {
             $val = self::where('key', $key)
                ->where('branch_id', $user->branch_id)
                ->value('value');
             if ($val !== null) return $val;
        }

        // 2. Fallback to Global (branch_id IS NULL)
        $val = self::where('key', $key)
            ->whereNull('branch_id')
            ->value('value');
            
        // 3. If totally missing, return default
        return $val ?? $default;
    }

    /**
     * Set a setting value by key.
     * Scoped to current user's branch if available.
     */
    public static function set($key, $value)
    {
        $branchId = auth()->user()->branch_id ?? null;
        
        return self::updateOrCreate(
            ['key' => $key, 'branch_id' => $branchId],
            ['value' => $value]
        );
    }
}
