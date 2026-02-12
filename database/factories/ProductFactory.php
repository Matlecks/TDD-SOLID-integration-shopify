<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ShopifyShop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'shopify_product_id' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'shopify_shop_id' => ShopifyShop::factory(),
            'title' => $this->faker->words(3, true),
            'body_html' => $this->faker->paragraph,
            'vendor' => $this->faker->company,
            'product_type' => $this->faker->word,
            'status' => $this->faker->randomElement(['active', 'draft', 'archived']),
            'handle' => $this->faker->slug,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function forShop(ShopifyShop $shop): static
    {
        return $this->state(fn (array $attributes) => [
            'shopify_shop_id' => $shop->id,
        ]);
    }
}
