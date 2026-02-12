<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'shopify_shop_id',
        'shopify_id',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'status',
        'handle',
        'published_at',
        'shopify_data',
    ];

    protected $casts = [
        'shopify_data' => 'array',
        'published_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class, 'shopify_shop_id');
    }
}
