<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function productVariation()
    {
        return $this->hasMany(ProductVariation::class);
    }

    protected static function booted()
    {
        static::deleting(function (Product $product) {

            if ($product->isForceDeleting()) {
                $product->images()->get()->each(function (Image $image) {
                    $image->forceDeleteWithFile();
                });
                $product->reviews()->forceDelete();
                $product->productVariation()->forceDelete();
            } else {
                $product->reviews()->delete();
                $product->productVariation()->delete();
            }
        });

        static::restoring(function (Product $product) {
            $product->reviews()->withTrashed()->restore();
            $product->productVariation()->withTrashed()->restore();
        });
    }
}
