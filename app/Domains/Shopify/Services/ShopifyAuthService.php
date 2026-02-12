<?php

namespace App\Domains\Shopify\Services;

use App\Domains\Shopify\Exceptions\ShopifyAuthException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyAuthService
{
    public function __construct()
    {
        $this->apiKey = config('shopify.api_key');
        $this->apiSecret = config('shopify.api_secret');
        $this->scopes = config('shopify.scopes', ['read_products', 'write_products']);
        $this->redirectUri = config('shopify.redirect_uri');
    }

    /**
     * Generates a URL for installing an app in the Shopify store
     *
     * @param string $shopDomain
     *
     * @return string
     */
    public function buildAuthUrl(string $shopDomain): string
    {
        $scopes = implode(',', $this->scopes);
        $state = bin2hex(random_bytes(12));

        $queryParams = http_build_query([
            'client_id' => $this->apiKey,
            'scope' => $scopes,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'grant_options' => [],
        ]);

        return "https://{$shopDomain}/admin/oauth/authorize?{$queryParams}";
    }

    /**
     * Validates the HMAC signature from the Shopify request
     *
     * @param array $params
     *
     * @return string
     */
    public function validateHmac(array $params): string
    {
        if (isset($params['hmac'])) {
            unset($params['hmac']);
        }

        ksort($params);

        return http_build_query($params);
    }

    /**
     * Obtains an access token from Shopify using a temporary authorization code.
     *
     * @param string $shopDomain
     * @param string $code
     *
     * @return array|RedirectResponse
     */
    public function getAccessData(string $shopDomain, string $code): array
    {
        $response = Http::withOptions([
            'verify' => false,
        ])->post("https://{$shopDomain}/admin/oauth/access_token", [
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'code' => $code,
        ]);

        $responseData = $response->json();

        if (!$response->successful()) {
            $error = $responseData['error'] ?? 'Unknown error';
            throw new ShopifyAuthException("Failed to obtain access token: {$error}");
        }

        if (!isset($responseData['access_token'])) {
            throw new ShopifyAuthException('Access token not found in response');
        }

        return [
            'access_token' => $responseData['access_token'],
            'scopes' => $responseData['scope'] ?? '',
        ];
    }
}
