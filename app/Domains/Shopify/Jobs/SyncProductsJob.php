<?php
namespace App\Domains\Shopify\Jobs;

use App\Domains\Shopify\DTOs\ProductData;
use App\Domains\Shopify\Services\ShopifyProductService;
use App\Models\Product;
use App\Models\ShopifyShop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public ?ShopifyShop $shop = null,
        public int $page = 1,
        public ?string $sinceId = null,
        public int $limit = 50
    ) {}

    public function handle(ShopifyProductService $shopifyService): void
    {
        try {
            Log::info('Starting Shopify products sync', [
                'shop_id' => $this->shop->id,
                'shop_domain' => $this->shop->domain,
                'page' => $this->page,
                'since_id' => $this->sinceId
            ]);

            $filters = [
                'limit' => $this->limit,
            ];

            if ($this->sinceId) {
                $filters['since_id'] = $this->sinceId;
            }

            $shopifyProducts = $shopifyService->listProducts($this->shop, $filters);

            if (empty($shopifyProducts)) {
                Log::info('No more products to sync', [
                    'shop_id' => $this->shop->id
                ]);
                return;
            }

            foreach ($shopifyProducts as $shopifyProduct) {
                $productData = ProductData::fromShopify($shopifyProduct);
                $this->syncProduct($productData);
            }

            $lastProduct = end($shopifyProducts);
            $nextSinceId = $lastProduct['id'] ?? null;

            if (count($shopifyProducts) >= $this->limit && $nextSinceId) {
                SyncProductsJob::dispatch(
                    $this->shop,
                    $this->page + 1,
                    $nextSinceId,
                    $this->limit
                )->delay(now()->addSeconds(1));
            }

            Log::info('Products sync completed for page', [
                'shop_id' => $this->shop->id,
                'page' => $this->page,
                'synced_count' => count($shopifyProducts)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync products from Shopify', [
                'shop_id' => $this->shop->id,
                'page' => $this->page,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function syncProduct(ProductData $productData): void
    {
        try {
            $product = Product::updateOrCreate(
                [
                    'shopify_shop_id' => $this->shop->id,
                    'shopify_id' => $productData->id,
                ],
                [
                    'title' => $productData->title,
                    'body_html' => $productData->body_html,
                    'vendor' => $productData->vendor,
                    'product_type' => $productData->product_type,
                    'status' => $productData->status,
                    'handle' => $productData->handle,
                    'published_at' => $productData->published_at,
                    'shopify_data' => $productData->shopify_data,
                ]
            );

            Log::info('Product synced successfully', [
                'shop_id' => $this->shop->id,
                'product_id' => $product->id,
                'shopify_product_id' => $productData->id,
                'title' => $productData->title
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync individual product', [
                'shop_id' => $this->shop->id,
                'shopify_product_id' => $productData->id,
                'title' => $productData->title,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
