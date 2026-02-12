<?php

namespace App\Domains\Shopify\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ProductData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $title,
        public readonly ?string $body_html,
        public readonly ?string $vendor,
        public readonly ?string $product_type,
        public readonly ?string $status,
        public readonly ?string $handle,
        public readonly ?string $tags,
        public readonly ?Carbon $published_at,
        public readonly ?array $variants,
        public readonly ?array $images,
        public readonly ?array $options,
        public readonly ?array $metafields,
        public readonly ?float $price,
        public readonly ?float $compare_at_price,
        public readonly ?string $sku,
        public readonly ?int $inventory_quantity,
        public readonly ?string $barcode,
        public readonly ?float $weight,
        public readonly ?string $weight_unit,
        public readonly ?array $shopify_data
    ) {}

    /**
     * Создает DTO из массива данных
     *
     * @param array $data
     * @return self
     * @throws ValidationException
     */
    public static function fromArray(array $data): self
    {
        // Валидация обязательных полей
        if (empty($data['title'])) {
            throw ValidationException::withMessages([
                'title' => 'Название продукта обязательно для заполнения'
            ]);
        }

        return new self(
            id: $data['id'] ?? null,
            title: $data['title'] ?? null,
            body_html: $data['body_html'] ?? $data['description'] ?? null,
            vendor: $data['vendor'] ?? null,
            product_type: $data['product_type'] ?? $data['type'] ?? null,
            status: $data['status'] ?? 'draft',
            handle: $data['handle'] ?? null,
            tags: self::formatTags($data['tags'] ?? null),
            published_at: self::parsePublishedAt($data['published_at'] ?? $data['published'] ?? null),
            variants: self::formatVariants($data['variants'] ?? []),
            images: self::formatImages($data['images'] ?? []),
            options: self::formatOptions($data['options'] ?? []),
            metafields: $data['metafields'] ?? [],
            price: $data['price'] ?? null,
            compare_at_price: $data['compare_at_price'] ?? null,
            sku: $data['sku'] ?? null,
            inventory_quantity: $data['inventory_quantity'] ?? 0,
            barcode: $data['barcode'] ?? null,
            weight: $data['weight'] ?? null,
            weight_unit: $data['weight_unit'] ?? 'kg',
            shopify_data: $data['shopify_data'] ?? null
        );
    }

    /**
     * Создает DTO из данных Shopify API
     *
     * @param array $shopifyProduct
     * @return self
     */
    public static function fromShopify(array $shopifyProduct): self
    {
        return new self(
            id: $shopifyProduct['id'] ?? null,
            title: $shopifyProduct['title'] ?? null,
            body_html: $shopifyProduct['body_html'] ?? null,
            vendor: $shopifyProduct['vendor'] ?? null,
            product_type: $shopifyProduct['product_type'] ?? null,
            status: $shopifyProduct['status'] ?? null,
            handle: $shopifyProduct['handle'] ?? null,
            tags: $shopifyProduct['tags'] ?? null,
            published_at: isset($shopifyProduct['published_at'])
                ? Carbon::parse($shopifyProduct['published_at'])
                : null,
            variants: $shopifyProduct['variants'] ?? [],
            images: $shopifyProduct['images'] ?? [],
            options: $shopifyProduct['options'] ?? [],
            metafields: $shopifyProduct['metafields'] ?? [],
            price: $shopifyProduct['variants'][0]['price'] ?? null,
            compare_at_price: $shopifyProduct['variants'][0]['compare_at_price'] ?? null,
            sku: $shopifyProduct['variants'][0]['sku'] ?? null,
            inventory_quantity: $shopifyProduct['variants'][0]['inventory_quantity'] ?? 0,
            barcode: $shopifyProduct['variants'][0]['barcode'] ?? null,
            weight: $shopifyProduct['variants'][0]['weight'] ?? null,
            weight_unit: $shopifyProduct['variants'][0]['weight_unit'] ?? 'kg',
            shopify_data: $shopifyProduct
        );
    }

    /**
     * Преобразует DTO в массив для Shopify API
     *
     * @return array
     */
    public function toShopifyArray(): array
    {
        $product = [
            'title' => $this->title,
            'body_html' => $this->body_html,
            'vendor' => $this->vendor,
            'product_type' => $this->product_type,
            'status' => $this->status,
            'handle' => $this->handle,
            'tags' => $this->tags,
        ];

        if ($this->id) {
            $product['id'] = $this->id;
        }

        if ($this->published_at) {
            $product['published_at'] = $this->published_at->toIso8601String();
        }

        if (!empty($this->variants)) {
            $product['variants'] = $this->variants;
        }

        if (!empty($this->images)) {
            $product['images'] = $this->images;
        }

        if (!empty($this->options)) {
            $product['options'] = $this->options;
        }

        // Удаляем null значения
        return array_filter($product, function ($value) {
            return !is_null($value) && $value !== [];
        });
    }

    /**
     * Преобразует DTO в массив для локального сохранения
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body_html' => $this->body_html,
            'vendor' => $this->vendor,
            'product_type' => $this->product_type,
            'status' => $this->status,
            'handle' => $this->handle,
            'tags' => $this->tags,
            'published_at' => $this->published_at,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'sku' => $this->sku,
            'inventory_quantity' => $this->inventory_quantity,
            'barcode' => $this->barcode,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'shopify_data' => $this->shopify_data,
        ];
    }

    /**
     * Форматирует теги
     *
     * @param string|array|null $tags
     * @return string|null
     */
    private static function formatTags($tags): ?string
    {
        if (is_null($tags)) {
            return null;
        }

        if (is_array($tags)) {
            return implode(', ', $tags);
        }

        return $tags;
    }

    /**
     * Парсит дату публикации
     *
     * @param string|Carbon|null $publishedAt
     * @return Carbon|null
     */
    private static function parsePublishedAt($publishedAt): ?Carbon
    {
        if (is_null($publishedAt)) {
            return null;
        }

        if ($publishedAt instanceof Carbon) {
            return $publishedAt;
        }

        if (is_bool($publishedAt)) {
            return $publishedAt ? Carbon::now() : null;
        }

        return Carbon::parse($publishedAt);
    }

    /**
     * Форматирует варианты
     *
     * @param array $variants
     * @return array
     */
    private static function formatVariants(array $variants): array
    {
        return array_values(array_map(function ($variant) {
            $formatted = [
                'price' => $variant['price'] ?? null,
                'compare_at_price' => $variant['compare_at_price'] ?? null,
                'sku' => $variant['sku'] ?? null,
                'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
                'inventory_management' => $variant['inventory_management'] ?? 'shopify',
                'inventory_policy' => $variant['inventory_policy'] ?? 'deny',
                'fulfillment_service' => $variant['fulfillment_service'] ?? 'manual',
                'weight' => $variant['weight'] ?? null,
                'weight_unit' => $variant['weight_unit'] ?? 'kg',
                'barcode' => $variant['barcode'] ?? null,
                'option1' => $variant['option1'] ?? null,
                'option2' => $variant['option2'] ?? null,
                'option3' => $variant['option3'] ?? null,
            ];

            return array_filter($formatted, function ($value) {
                return !is_null($value);
            });
        }, $variants));
    }

    /**
     * Форматирует изображения
     *
     * @param array $images
     * @return array
     */
    private static function formatImages(array $images): array
    {
        return array_values(array_map(function ($image) {
            $formatted = [
                'src' => $image['src'] ?? $image['url'] ?? null,
                'position' => $image['position'] ?? null,
                'alt' => $image['alt'] ?? null,
            ];

            if (isset($image['variant_ids'])) {
                $formatted['variant_ids'] = $image['variant_ids'];
            }

            if (isset($image['width'])) {
                $formatted['width'] = $image['width'];
            }

            if (isset($image['height'])) {
                $formatted['height'] = $image['height'];
            }

            return array_filter($formatted, function ($value) {
                return !is_null($value);
            });
        }, $images));
    }

    /**
     * Форматирует опции
     *
     * @param array $options
     * @return array
     */
    private static function formatOptions(array $options): array
    {
        return array_values(array_map(function ($option) {
            $formatted = [
                'name' => $option['name'] ?? null,
                'position' => $option['position'] ?? null,
            ];

            if (isset($option['values'])) {
                $formatted['values'] = is_array($option['values'])
                    ? $option['values']
                    : explode(',', $option['values']);
            }

            return array_filter($formatted, function ($value) {
                return !is_null($value);
            });
        }, $options));
    }

    /**
     * Обновляет DTO новыми данными
     *
     * @param array $data
     * @return self
     */
    public function merge(array $data): self
    {
        return self::fromArray(array_merge(
            $this->toArray(),
            $data
        ));
    }

    /**
     * Проверяет, опубликован ли продукт
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === 'active' && !is_null($this->published_at);
    }

    /**
     * Проверяет, есть ли у продукта варианты
     *
     * @return bool
     */
    public function hasVariants(): bool
    {
        return count($this->variants) > 0;
    }

    /**
     * Получает первый вариант продукта
     *
     * @return array|null
     */
    public function getFirstVariant(): ?array
    {
        return $this->variants[0] ?? null;
    }

    /**
     * Получает основное изображение продукта
     *
     * @return array|null
     */
    public function getMainImage(): ?array
    {
        foreach ($this->images as $image) {
            if (($image['position'] ?? 0) === 1) {
                return $image;
            }
        }

        return $this->images[0] ?? null;
    }

    /**
     * Создает DTO для простого продукта (без вариантов)
     *
     * @param array $data
     * @return self
     */
    public static function createSimple(array $data): self
    {
        return self::fromArray(array_merge($data, [
            'variants' => [
                [
                    'price' => $data['price'] ?? null,
                    'compare_at_price' => $data['compare_at_price'] ?? null,
                    'sku' => $data['sku'] ?? null,
                    'inventory_quantity' => $data['inventory_quantity'] ?? 0,
                    'barcode' => $data['barcode'] ?? null,
                    'weight' => $data['weight'] ?? null,
                    'weight_unit' => $data['weight_unit'] ?? 'kg',
                ]
            ],
            'options' => [
                [
                    'name' => 'Title',
                    'values' => ['Default Title'],
                    'position' => 1,
                ]
            ],
            'shopify_data' => $data['shopify_data'] ?? null,
        ]));
    }

    /**
     * Создает DTO для черновика продукта
     *
     * @param string $title
     * @return self
     */
    public static function createDraft(string $title): self
    {
        return self::fromArray([
            'title' => $title,
            'status' => 'draft',
            'published_at' => null,
            'inventory_quantity' => 0,
            'shopify_data' => null,
        ]);
    }
}
