<?php

namespace App\Models\Traits;

use App\Domain\Auth\Services\BranchContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope('branch_scope', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return;
            }

            $currentBranchId = app(BranchContext::class)->getCurrentBranchId();

            if ($currentBranchId) {
                $builder->where($builder->getQuery()->from . '.branch_id', $currentBranchId);
            }
        });

        static::creating(function ($model) {
            if (! $model->branch_id) {
                $model->branch_id = app(BranchContext::class)->getCurrentBranchId();
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
