<?php

namespace Database\Factories;

use App\Models\ShopifyShop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopifyShopFactory extends Factory
{
    protected $model = ShopifyShop::class;

    public function definition(): array
    {
        return [
            'shopify_shop_id' => $this->faker->unique()->numberBetween(100000, 999999),
            'name' => $this->faker->company,
            'email' => $this->faker->email,
            'domain' => $this->faker->domainName,
            'shopify_domain' => $this->faker->domainName,
            'access_token' => $this->faker->sha256,
            'scopes' => 'read_products,write_products',
            'installed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
