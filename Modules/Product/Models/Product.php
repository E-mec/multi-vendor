<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Authentication\Models\Vendor;
use Modules\Product\Database\Factories\ProductFactory;
use Modules\Product\Enums\ProductStatusEnum;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'status'
    ];

    protected $casts = [
        'status' => ProductStatusEnum::class
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeSearch($query, $search)
    {
        if (!$search) return $query;

        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatusEnum::ACTIVE);
    }

     protected static function newFactory(): ProductFactory
     {
          return ProductFactory::new();
     }
}
