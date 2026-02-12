<?php

namespace Tests\Unit\Domains\Shopify\Actions;

use App\Domains\Shopify\Actions\HandleShopifyCallbackAction;
use App\Domains\Shopify\Exceptions\ShopifyAuthException;
use App\Domains\Shopify\Requests\HandleShopifyCallbackRequest;
use App\Domains\Shopify\Services\ShopifyAuthService;
use App\Models\ShopifyShop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleShopifyCallbackActionTest extends TestCase
{
    use RefreshDatabase;

    private ShopifyAuthService $authService;
    private HandleShopifyCallbackAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        Crypt::shouldReceive('encrypt')
            ->andReturnUsing(fn($value) => $value);
        Crypt::shouldReceive('decrypt')
            ->andReturnUsing(fn($value) => $value);

        $this->authService = Mockery::mock(ShopifyAuthService::class);
        $this->action = new HandleShopifyCallbackAction($this->authService);
    }

    #[Test]
    public function it_successfully_handles_callback_and_saves_shop()
    {
        // Arrange
        $shopDomain = 'ndpmsu-41.myshopify.com';
        $authCode = 'valid_auth_code';
        $accessToken = 'test_access_token_123';

        $request = Mockery::mock(HandleShopifyCallbackRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn([
                'shop' => $shopDomain,
                'code' => $authCode,
                'timestamp' => time(),
                'hmac' => 'valid_hmac'
            ]);

        $this->authService->shouldReceive('getAccessData')
            ->with($shopDomain, $authCode)
            ->once()
            ->andReturn([
                'access_token' => $accessToken,
                'scopes' => 'read_products,write_products'
            ]);

        Http::fake([
            "https://{$shopDomain}/admin/api/2026-01/shop.json" => Http::response([
                'shop' => [
                    'id' => 123456,
                    'name' => 'Test Store',
                    'email' => 'store@test.com',
                    'domain' => $shopDomain,
                    'myshopify_domain' => $shopDomain,
                    'currency' => 'USD',
                    'timezone' => 'America/New_York',
                    'iana_timezone' => 'America/New_York',
                    'shop_owner' => 'John Doe',
                    'address1' => '123 Test St',
                    'city' => 'Test City',
                    'country' => 'US',
                    'phone' => '+1234567890',
                ]
            ], 200)
        ]);

        // Act
        $shop = $this->action->__invoke($request);

        // Assert
        $this->assertInstanceOf(ShopifyShop::class, $shop);
        $this->assertEquals(123456, $shop->shopify_shop_id);
        $this->assertEquals($shopDomain, $shop->domain);
        $this->assertEquals($accessToken, $shop->access_token);
        $this->assertEquals('Test Store', $shop->name);
    }

    #[Test]
    public function it_throws_exception_when_shop_data_fetch_fails()
    {
        // Arrange
        $shopDomain = 'ndpmsu-41.myshopify.com';
        $authCode = 'valid_auth_code';
        $accessToken = 'test_access_token_123';

        $request = Mockery::mock(HandleShopifyCallbackRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn([
                'shop' => $shopDomain,
                'code' => $authCode
            ]);

        $this->authService->shouldReceive('getAccessData')
            ->with($shopDomain, $authCode)
            ->once()
            ->andReturn(['access_token' => $accessToken]);

        Http::fake([
            "https://{$shopDomain}/admin/api/2026-01/shop.json" => Http::response([
                'error' => 'Invalid API key'
            ], 401)
        ]);

        // Assert
        $this->expectException(ShopifyAuthException::class);
        $this->expectExceptionMessage('Failed to fetch shop data');

        // Act
        $this->action->__invoke($request);
    }

    #[Test]
    public function it_updates_existing_shop_instead_of_creating_new()
    {
        // Arrange
        $shopDomain = 'ndpmsu-41.myshopify.com';
        $shopifyId = 123456;

        $existingShop = ShopifyShop::factory()->create([
            'shopify_shop_id' => $shopifyId,
            'shopify_domain' => $shopDomain,
            'domain' => $shopDomain,
            'access_token' => 'old_token'
        ]);

        $request = Mockery::mock(HandleShopifyCallbackRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn([
                'shop' => $shopDomain,
                'code' => 'auth_code'
            ]);

        $this->authService->shouldReceive('getAccessData')
            ->once()
            ->andReturn([
                'access_token' => 'new_token_456',
                'scopes' => 'read_products'
            ]);

        Http::fake([
            "https://{$shopDomain}/admin/api/2026-01/shop.json" => Http::response([
                'shop' => [
                    'id' => $shopifyId,
                    'name' => 'Updated Store Name',
                    'email' => 'updated@test.com',
                    'domain' => $shopDomain,
                    'myshopify_domain' => $shopDomain,
                ]
            ], 200)
        ]);

        // Act
        $updatedShop = $this->action->__invoke($request);

        // Assert
        $this->assertEquals($existingShop->id, $updatedShop->id);
        $this->assertEquals('new_token_456', $updatedShop->access_token);
        $this->assertEquals('Updated Store Name', $updatedShop->name);
        $this->assertEquals('updated@test.com', $updatedShop->email);

        $this->assertEquals(1, ShopifyShop::count());
    }
}
