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

    public int $timeout = 600; // 10 минут
    public int $tries = 3;
    public int $backoff = 5; // секунды между повторами

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

            // Получаем товары из Shopify
            $shopifyProducts = $shopifyService->listProducts($this->shop, $filters);

            if (empty($shopifyProducts)) {
                Log::info('No more products to sync', [
                    'shop_id' => $this->shop->id
                ]);
                return;
            }



            // Обрабатываем каждый товар
            foreach ($shopifyProducts as $shopifyProduct) {

                Log::debug('Shopify $shopifyProduct', [
                    '$shopifyProduct' => json_encode($shopifyProduct, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ]);


                $productData = ProductData::fromShopify($shopifyProduct);
                $this->syncProduct($productData);
            }

            // Получаем последний ID для следующей страницы
            $lastProduct = end($shopifyProducts);
            $nextSinceId = $lastProduct['id'] ?? null;

            // Если получили полную страницу, запускаем следующую
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

            // Пробрасываем исключение для повторной попытки
            throw $e;
        }
    }

    private function syncProduct(ProductData $productData): void
    {
        try {
            // Обновляем или создаем товар
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

            // Синхронизируем варианты
            $this->syncVariants($product, $productData->variants);

            // Синхронизируем изображения
            $this->syncImages($product, $productData->images);

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

    private function syncVariants(Product $product, array $variants): void
    {
        // Удаляем старые варианты
        $product->variants()->delete();

        // Создаем новые варианты
        foreach ($variants as $variantData) {
            $product->variants()->create([
                'shopify_variant_id' => $variantData['id'] ?? null,
                'title' => $variantData['title'] ?? '',
                'price' => $variantData['price'] ?? 0,
                'sku' => $variantData['sku'] ?? null,
                'inventory_quantity' => $variantData['inventory_quantity'] ?? 0,
                'inventory_item_id' => $variantData['inventory_item_id'] ?? null,
                'option1' => $variantData['option1'] ?? null,
                'option2' => $variantData['option2'] ?? null,
                'option3' => $variantData['option3'] ?? null,
                'shopify_data' => $variantData,
            ]);
        }
    }

    private function syncImages(Product $product, array $images): void
    {
        // Удаляем старые изображения
        $product->images()->delete();

        // Создаем новые изображения
        foreach ($images as $imageData) {
            $product->images()->create([
                'shopify_image_id' => $imageData['id'] ?? null,
                'src' => $imageData['src'] ?? null,
                'position' => $imageData['position'] ?? 0,
                'alt' => $imageData['alt'] ?? null,
                'shopify_data' => $imageData,
            ]);
        }
    }
}
