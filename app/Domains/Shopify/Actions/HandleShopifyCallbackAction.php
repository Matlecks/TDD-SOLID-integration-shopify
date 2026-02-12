<?php

namespace App\Domains\Shopify\Actions;

use App\Domains\Shopify\DTOs\ShopData;
use App\Domains\Shopify\Exceptions\ShopifyAuthException;
use App\Domains\Shopify\Requests\HandleShopifyCallbackRequest;
use App\Domains\Shopify\Services\ShopifyAuthService;
use App\Http\Controllers\Controller;
use App\Models\ShopifyShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HandleShopifyCallbackAction extends Controller
{
    public function __construct(
        private readonly ShopifyAuthService $shopifyAuthService
    ) {
    }

    /**
     * Handles a callback from Shopify after the app is installed.
     *
     * @param Request $request
     *
     * @return ShopifyShop
     */
    public function __invoke(HandleShopifyCallbackRequest $request): ShopifyShop
    {
        $validatedData = $request->validated();

        $accessData = $this->shopifyAuthService->getAccessData(
            $validatedData['shop'],
            $validatedData['code']
        );

        $shopData = $this->fetchShopData(
            $validatedData['shop'],
            $accessData['access_token']
        );

        $shopDto = ShopData::fromApiResponse($shopData, $accessData);

        return $this->persistShop($shopDto);
    }

    /**
     * Requests store data from the Shopify API
     *
     * @param string $shop
     * @param string $accessToken
     *
     * @return array
     * @throws ShopifyAuthException
     */
    private function fetchShopData(string $shop, string $accessToken): array
    {
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("https://{$shop}/admin/api/2026-01/shop.json");

        if (!$response->successful()) {
            throw new ShopifyAuthException(
                'Failed to fetch shop data: ' . $response->body()
            );
        }

        return $response->json('shop');
    }

    /**
     * Saves or updates a store in the database
     *
     * @param ShopData $shopDto
     *
     * @return ShopifyShop
     */
    private function persistShop(ShopData $shopDto): ShopifyShop
    {
        return ShopifyShop::updateOrCreate(
            ['shopify_shop_id' => $shopDto->shopifyId],
            $shopDto->toArray()
        );
    }
}
