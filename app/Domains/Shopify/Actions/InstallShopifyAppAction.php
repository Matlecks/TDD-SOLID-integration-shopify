<?php

namespace App\Domains\Shopify\Actions;


use App\Domains\Shopify\Services\ShopifyAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Domains\Shopify\Requests\InstallShopifyAppRequest;

class InstallShopifyAppAction extends Controller
{
    public function __construct(
        private readonly ShopifyAuthService $shopifyAuthService
    ) {}

    /**
     * Generates a URL to install the application
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function __invoke(InstallShopifyAppRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        $shopDomain = $validatedData['shop'];
        $redirectUri = $this->shopifyAuthService->buildAuthUrl($shopDomain);

        return redirect()->away($redirectUri);
    }
}
