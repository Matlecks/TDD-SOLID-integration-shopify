<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopify_id' => $this->shopify_id,
            'title' => $this->title,
            'body_html' => $this->body_html,
            'vendor' => $this->vendor,
            'product_type' => $this->product_type,
            'handle' => $this->handle,
            'status' => $this->status,
            'published_scope' => $this->published_scope,
            'tags' => $this->tags ? explode(',', $this->tags) : [],
            'tags_string' => $this->tags,

            // Даты Shopify
            'shopify_created_at' => $this->shopify_created_at?->toISOString(),
            'shopify_updated_at' => $this->shopify_updated_at?->toISOString(),

            // Системные даты
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Связи
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'variants' => VariantResource::collection($this->whenLoaded('variants')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'options' => OptionResource::collection($this->whenLoaded('options')),

            // Метаданные
            'meta' => [
                'has_variants' => $this->variants_count > 0 ?? false,
                'has_images' => $this->images_count > 0 ?? false,
                'inventory_total' => $this->whenLoaded('variants', function () {
                    return $this->variants->sum('inventory_quantity');
                }),
                'price_range' => $this->whenLoaded('variants', function () {
                    $prices = $this->variants->pluck('price')->filter();

                    if ($prices->isEmpty()) {
                        return null;
                    }

                    return [
                        'min' => (float) $prices->min(),
                        'max' => (float) $prices->max(),
                        'currency' => 'USD', // или из настроек магазина
                    ];
                }),
            ],

            // Ссылки (HATEOAS)
            'links' => [
                'self' => route('api.v1.products.show', $this->id),
                'update' => route('api.v1.products.update', $this->id),
                'delete' => route('api.v1.products.destroy', $this->id),
                'shop' => $this->shop_id ? route('api.v1.shops.show', $this->shop_id) : null,
            ],
        ];
    }

    /**
     * Дополнительные данные, добавляемые к ответу.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => 'success',
            'timestamp' => now()->toISOString(),
            'api_version' => 'v1',
        ];
    }
}
