<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\BelongsToBranch;

class Tag extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'name',
        'is_active',
        'branch_id',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag')->using(ProductTag::class);
    }
}
