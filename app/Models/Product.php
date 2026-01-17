<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\BelongsToBranch;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends BaseModel implements HasMedia
{
    use HasFactory, SoftDeletes, BelongsToBranch, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'category_id',
        'tax_id',
        'brand_id',
        'created_by',
        'branch_id',

        'name',
        'slug',
        'thumbnail',
        'images',

        'sku',
        'barcode',
        'code',

        'base_price',
        'base_discount_price',

        'type',

        'weight',
        'dimensions',
        'materials',

        'description',
        'additional_info',

        'is_active',

        'meta_title',
        'meta_description',
        'meta_keywords',
    ];


    protected $casts = [
        'is_active' => 'boolean',

        'base_price' => 'decimal:2',
        'base_discount_price' => 'decimal:2',

        'dimensions' => 'array',
        'materials' => 'array',
        'images' => 'array',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag')->using(ProductTag::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            'product_variation_attributes',
            'product_id',
            'attribute_id'
        )->distinct();
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function scopeWithActiveVariations($query)
    {
        return $query->whereHas('variations', function ($q) {
            $q->where('is_active', true);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper methods
    |--------------------------------------------------------------------------
    */

    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }

    public function getCurrentPrice(): float
    {
        return $this->base_discount_price ?? $this->base_price;
    }

    public function hasVariations(): bool
    {
        return $this->variations()->count() > 0;
    }

    public function getAttributeValues(ProductAttribute $attribute)
    {
        return ProductAttributeValue::whereHas('variations', function ($query) {
            $query->where('product_id', $this->id);
        })
            ->where('attribute_id', $attribute->id)
            ->get();
    }

    public function findVariationByAttributes(array $attributeValueIds)
    {
        return $this->variations()
            ->whereHas(
                'attributeValues',
                function ($query) use ($attributeValueIds) {
                    $query->whereIn('product_attribute_values.id', $attributeValueIds);
                },
                '=',
                count($attributeValueIds)
            )
            ->first();
    }

    public function hasDiscount(): bool
    {
        return !is_null($this->base_discount_price);
    }

    public function getDiscountPercentage(): ?float
    {
        if (!$this->hasDiscount()) {
            return null;
        }

        return round(
            (($this->base_price - $this->base_discount_price) / $this->base_price) * 100
        );
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
