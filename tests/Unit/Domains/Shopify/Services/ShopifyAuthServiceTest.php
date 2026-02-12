<?php

namespace Tests\Unit\Domains\Shopify\Services;

use App\Domains\Shopify\Exceptions\ShopifyAuthException;
use App\Domains\Shopify\Services\ShopifyAuthService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ShopifyAuthServiceTest extends TestCase
{
    private ShopifyAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('shopify.api_key', 'test_api_key');
        Config::set('shopify.api_secret', 'test_api_secret');
        Config::set('shopify.redirect_uri', 'https://test.com/callback');
        Config::set('shopify.scopes', ['read_products', 'write_products']);

        $this->service = new ShopifyAuthService();
    }

    #[Test]
    public function it_builds_correct_authorization_url()
    {
        $shopDomain = 'ndpmsu-41.myshopify.com';

        $url = $this->service->buildAuthUrl($shopDomain);

        $this->assertStringStartsWith("https://{$shopDomain}/admin/oauth/authorize", $url);
        $this->assertStringContainsString('client_id=test_api_key', $url);
        $this->assertStringContainsString('scope=read_products%2Cwrite_products', $url);
        $this->assertStringContainsString('redirect_uri=' . urlencode('https://test.com/callback'), $url);
        $this->assertStringContainsString('state=', $url);
    }

    #[Test]
    public function it_validates_shop_domain_format()
    {
        $this->expectException(ShopifyAuthException::class);
        $this->expectExceptionMessage('Invalid shop domain format');

        $this->service->buildAuthUrl('invalid-domain');
    }

    #[Test]
    public function it_successfully_obtains_access_token()
    {
        $shopDomain = 'ndpmsu-41.myshopify.com';
        $authCode = 'valid_auth_code';

        Http::fake([
            "https://{$shopDomain}/admin/oauth/access_token" => Http::response([
                'access_token' => 'test_access_token_123',
                'scope' => 'read_products,write_products',
                'expires_in' => 86400
            ], 200)
        ]);

        $accessData = $this->service->getAccessData($shopDomain, $authCode);

        $this->assertEquals('test_access_token_123', $accessData['access_token']);
        $this->assertEquals('read_products,write_products', $accessData['scopes']);
    }

    #[Test]
    public function it_throws_exception_when_token_request_fails()
    {
        $shopDomain = 'ndpmsu-41.myshopify.com';
        $authCode = 'invalid_code';

        Http::fake([
            "https://{$shopDomain}/admin/oauth/access_token" => Http::response([
                'error' => 'invalid_request'
            ], 400)
        ]);

        $this->expectException(ShopifyAuthException::class);

        $this->service->getAccessData($shopDomain, $authCode);
    }

}
