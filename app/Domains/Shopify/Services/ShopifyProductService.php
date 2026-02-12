<?php

namespace App\Domains\Shopify\Services;

use App\Domains\Shopify\DTOs\ProductData;
use App\Models\ShopifyShop;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class ShopifyProductService
{
    public function __construct(
        private Client $client,
        private ShopifyAuthService $authService
    ) {
    }

    /**
     * Creates a product in Shopify
     *
     * @param ShopifyShop $shop
     * @param ProductData $productData
     * @return array
     * @throws GuzzleException
     * @throws RequestException
     */
    public function createProduct(ShopifyShop $shop, ProductData $productData): array
    {
        $accessToken = decrypt($shop->access_token);

        $payload = [
            'product' => [
                'title' => $productData->title,
                'body_html' => $productData->body_html,
                'vendor' => $productData->vendor,
                'product_type' => $productData->product_type,
                'status' => $productData->status,
                'variants' => $this->formatVariants($productData->variants ?? []),
                'images' => $this->formatImages($productData->images ?? []),
                'options' => $this->formatOptions($productData->options ?? []),
                'tags' => $productData->tags ?? '',
            ]
        ];

        try {
            $response = $this->client->post(
                "https://{$shop->domain}/admin/api/2024-01/products.json",
                [
                    'headers' => [
                        'X-Shopify-Access-Token' => $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Product created in Shopify', [
                'shop_id' => $shop->id,
                'shopify_product_id' => $responseData['product']['id'] ?? null
            ]);

            return $responseData['product'];
        } catch (GuzzleException $e) {
            Log::error('Failed to create product in Shopify', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
                'title' => $productData->title
            ]);

            throw $e;
        }
    }

    /**
     * Updates a product in Shopify
     *
     * @param ShopifyShop $shop
     * @param int $shopifyProductId
     * @param ProductData $productData
     * @return array
     * @throws GuzzleException
     */
    public function updateProduct(ShopifyShop $shop, int $shopifyProductId, ProductData $productData): array
    {
        $accessToken = decrypt($shop->access_token);

        $payload = [
            'product' => [
                'id' => $shopifyProductId,
                'title' => $productData->title,
                'body_html' => $productData->body_html,
                'vendor' => $productData->vendor,
                'product_type' => $productData->product_type,
                'status' => $productData->status,
                'tags' => $productData->tags ?? '',
            ]
        ];

        try {
            $response = $this->client->put(
                "https://{$shop->domain}/admin/api/2024-01/products/{$shopifyProductId}.json",
                [
                    'headers' => [
                        'X-Shopify-Access-Token' => $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Product updated in Shopify', [
                'shop_id' => $shop->id,
                'shopify_product_id' => $shopifyProductId
            ]);

            return $responseData['product'];
        } catch (GuzzleException $e) {
            Log::error('Failed to update product in Shopify', [
                'shop_id' => $shop->id,
                'shopify_product_id' => $shopifyProductId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Removes a product from Shopify
     *
     * @param ShopifyShop $shop
     * @param int $shopifyProductId
     * @throws GuzzleException
     */
    public function deleteProduct(ShopifyShop $shop, int $shopifyProductId): void
    {
        $accessToken = decrypt($shop->access_token);

        try {
            $this->client->delete(
                "https://{$shop->domain}/admin/api/2024-01/products/{$shopifyProductId}.json",
                [
                    'headers' => [
                        'X-Shopify-Access-Token' => $accessToken,
                    ],
                ]
            );

            Log::info('Product deleted from Shopify', [
                'shop_id' => $shop->id,
                'shopify_product_id' => $shopifyProductId
            ]);
        } catch (GuzzleException $e) {
            Log::error('Failed to delete product from Shopify', [
                'shop_id' => $shop->id,
                'shopify_product_id' => $shopifyProductId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Gets product from Shopify
     *
     * @param ShopifyShop $shop
     * @param int $shopifyProductId
     * @return array
     * @throws GuzzleException
     */
    public function getProduct(ShopifyShop $shop, int $shopifyProductId): array
    {
        $accessToken = decrypt($shop->access_token);

        try {
            $response = $this->client->get(
                "https://{$shop->domain}/admin/api/2024-01/products/{$shopifyProductId}.json",
                [
                    'headers' => [
                        'X-Shopify-Access-Token' => $accessToken,
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            return $responseData['product'];
        } catch (GuzzleException $e) {
            Log::error('Failed to get product from Shopify', [
                'shop_id' => $shop->id,
                'shopify_product_id' => $shopifyProductId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Gets a list of products from Shopify
     *
     * @param ShopifyShop $shop
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function listProducts(ShopifyShop $shop, array $filters = []): array
    {
        $accessToken = decrypt($shop->access_token);

        $query = http_build_query($filters);
        $url = "https://{$shop->domain}/admin/api/2024-01/products.json";

        if (!empty($query)) {
            $url .= '?' . $query;
        }

        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $accessToken,
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::debug('Shopify API Full Response', [
                'shop_id' => $shop->id,
                'response_data' => json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ]);

            return $responseData['products'] ?? [];
        } catch (GuzzleException $e) {
            Log::error('Failed to list products from Shopify', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Formats product variants for the Shopify API
     *
     * @param array $variants
     * @return array
     */
    private function formatVariants(array $variants): array
    {
        return array_map(function ($variant) {
            $formatted = [
                'price' => $variant['price'] ?? null,
                'sku' => $variant['sku'] ?? null,
                'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
                'option1' => $variant['option1'] ?? null,
                'option2' => $variant['option2'] ?? null,
                'option3' => $variant['option3'] ?? null,
            ];

            // Удаляем null значения
            return array_filter($formatted, function ($value) {
                return !is_null($value);
            });
        }, $variants);
    }

    /**
     * Formats images for the Shopify API
     *
     * @param array $images
     * @return array
     */
    private function formatImages(array $images): array
    {
        return array_map(function ($image) {
            return [
                'src' => $image['src'] ?? null,
                'position' => $image['position'] ?? null,
                'alt' => $image['alt'] ?? null,
            ];
        }, $images);
    }

    /**
     * Formats options for the Shopify API
     *
     * @param array $options
     * @return array
     */
    private function formatOptions(array $options): array
    {
        return array_map(function ($option) {
            return [
                'name' => $option['name'] ?? null,
                'values' => $option['values'] ?? [],
                'position' => $option['position'] ?? null,
            ];
        }, $options);
    }

    /**
     * Checks the availability of the Shopify API
     *
     * @param ShopifyShop $shop
     * @return bool
     */
    public function checkApiAccess(ShopifyShop $shop): bool
    {
        try {
            $this->listProducts($shop, ['limit' => 1]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Syncs product quantities with Shopify
     *
     * @param ShopifyShop $shop
     * @param int $inventoryItemId
     * @param int $quantity
     * @throws GuzzleException
     */
    public function updateInventoryLevel(ShopifyShop $shop, int $inventoryItemId, int $quantity): void
    {
        $accessToken = decrypt($shop->access_token);

        try {
            $this->client->post(
                "https://{$shop->domain}/admin/api/2024-01/inventory_levels/set.json",
                [
                    'headers' => [
                        'X-Shopify-Access-Token' => $accessToken,
                    ],
                    'json' => [
                        'inventory_item_id' => $inventoryItemId,
                        'location_id' => $this->getDefaultLocationId($shop),
                        'available' => $quantity,
                    ],
                ]
            );

            Log::info('Inventory level updated in Shopify', [
                'shop_id' => $shop->id,
                'inventory_item_id' => $inventoryItemId,
                'quantity' => $quantity
            ]);
        } catch (GuzzleException $e) {
            Log::error('Failed to update inventory level in Shopify', [
                'shop_id' => $shop->id,
                'inventory_item_id' => $inventoryItemId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Gets the default location ID
     *
     * @param ShopifyShop $shop
     * @return int
     * @throws GuzzleException
     */
    private function getDefaultLocationId(ShopifyShop $shop): int
    {
        $accessToken = decrypt($shop->access_token);

        $response = $this->client->get(
            "https://{$shop->domain}/admin/api/2024-01/locations.json",
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $accessToken,
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['locations'][0]['id'] ?? 0;
    }
}
