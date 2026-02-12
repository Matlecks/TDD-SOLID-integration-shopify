<?php

namespace App\Domains\Shopify\Jobs;

use App\Models\ShopifyShop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchShopifyShopDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ShopifyShop $shop
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $accessToken = Crypt::decrypt($this->shop->access_token);
            $shopDomain = $this->shop->shopify_domain ?? $this->shop->domain;
            $apiVersion = config('shopify.api_version', '2026-01');

            $this->fetchAndSyncShopData($shopDomain, $accessToken, $apiVersion);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Shopify shop data', [
                'shop_id' => $this->shop->id,
                'shop_domain' => $this->shop->domain,
                'error' => $e->getMessage()
            ]);

            $this->fail($e);
        }
    }

    /**
     * Fetch shop data from Shopify API and sync with database
     */
    private function fetchAndSyncShopData(string $shopDomain, string $accessToken, string $apiVersion): void
    {
        $response = Http::withOptions([
            'verify' => false,
            'timeout' => 30,
        ])->withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->get("https://{$shopDomain}/admin/api/{$apiVersion}/shop.json");

        if ($response->failed()) {
            throw new \Exception("Shopify API request failed with status: {$response->status()}");
        }

        $shopData = $response->json('shop');

        if (!$shopData) {
            throw new \Exception('Invalid shop data received from Shopify API');
        }

        $existingShop = ShopifyShop::where('shopify_shop_id', $shopData['id'])
            ->orWhere('domain', $shopData['domain'])
            ->orWhere('shopify_domain', $shopData['myshopify_domain'] ?? null)
            ->first();

        $shopAttributes = [
            'shopify_shop_id' => $shopData['id'],
            'domain' => $shopData['domain'],
            'shopify_domain' => $shopData['myshopify_domain'] ?? null,
            'name' => $shopData['name'] ?? null,
            'email' => $shopData['email'] ?? null,
            'currency' => $shopData['currency'] ?? null,
            'timezone' => $shopData['iana_timezone'] ?? $shopData['timezone'] ?? null,
            'phone' => $shopData['phone'] ?? null,
            'country' => $shopData['country'] ?? null,
            'country_code' => $shopData['country_code'] ?? null,
            'country_name' => $shopData['country_name'] ?? null,
            'weight_unit' => $shopData['weight_unit'] ?? null,
            'primary_locale' => $shopData['primary_locale'] ?? null,
            'plan_name' => $shopData['plan_name'] ?? $shopData['plan_display_name'] ?? null,
            'customer_email' => $shopData['customer_email'] ?? null,
            'created_at_shopify' => $shopData['created_at'] ?? null,
            'updated_at_shopify' => $shopData['updated_at'] ?? null,
            'is_active' => true,
            'last_sync_at' => now(),
        ];

        if ($existingShop) {
            $existingShop->update($shopAttributes);

            Log::info('Shopify shop updated successfully', [
                'shop_id' => $existingShop->id,
                'shop_domain' => $existingShop->domain,
                'shopify_shop_id' => $shopData['id']
            ]);

            $this->logInfo("Shop updated: {$existingShop->domain}");
        } else {

            $shopAttributes['access_token'] = $this->shop->access_token;
            $shopAttributes['scopes'] = $this->shop->scopes ?? config('shopify.scopes', []);
            $shopAttributes['installed_at'] = now();

            $newShop = ShopifyShop::create($shopAttributes);

            Log::info('New Shopify shop created', [
                'shop_id' => $newShop->id,
                'shop_domain' => $newShop->domain,
                'shopify_shop_id' => $shopData['id']
            ]);

            $this->logInfo("New shop created: {$newShop->domain}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('FetchShopifyShopDataJob failed', [
            'shop_id' => $this->shop->id,
            'shop_domain' => $this->shop->domain,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->shop->update([
            'last_sync_failed_at' => now(),
            'last_sync_error' => $exception->getMessage()
        ]);
    }

    /**
     * Log info message with context
     */
    private function logInfo(string $message): void
    {
        $this->info?->call($message);
        Log::info($message, ['shop_id' => $this->shop->id]);
    }
}
