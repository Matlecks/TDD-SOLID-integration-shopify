<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_shop_id',
        'domain',
        'shopify_domain',
        'name',
        'email',
        'access_token',
        'scopes',
        'plan_name',
        'is_active',
        'installed_at',
        'uninstalled_at',
        'last_synced_at',
        'meta',
    ];

    protected $casts = [
        'scopes' => 'array',
        'meta' => 'array',
        'installed_at' => 'datetime',
        'uninstalled_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getAccessTokenAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? encrypt($value) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('installed_at')
            ->whereNull('uninstalled_at');
    }
}
