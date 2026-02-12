<?php

namespace Tests\Unit\Domains\Shopify\DTOs;

use App\Domains\Shopify\DTOs\ShopData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopDataTest extends TestCase
{
    #[Test]
    public function it_creates_dto_from_api_response()
    {
        // Arrange
        $apiData = [
            'id' => 123456,
            'name' => 'Test Store',
            'email' => 'test@example.com',
            'domain' => 'test-store.myshopify.com',
            'myshopify_domain' => 'test-store.myshopify.com',
            'currency' => 'USD',
            'timezone' => 'America/New_York',
        ];

        $accessData = [
            'access_token' => 'test_token_123',
            'scopes' => 'read_products,write_products',
        ];

        // Act
        $shopData = ShopData::fromApiResponse($apiData, $accessData);

        // Assert
        $this->assertEquals(123456, $shopData->shopifyId);
        $this->assertEquals('Test Store', $shopData->name);
        $this->assertEquals('test@example.com', $shopData->email);
        $this->assertEquals('test-store.myshopify.com', $shopData->domain);
        $this->assertEquals('test-store.myshopify.com', $shopData->shopifyDomain);
        $this->assertEquals('test_token_123', $shopData->accessToken);
        $this->assertEquals(['read_products', 'write_products'], $shopData->scopes);
        $this->assertEquals('USD', $shopData->currency);
        $this->assertEquals('America/New_York', $shopData->timezone);
        $this->assertTrue($shopData->isActive);
    }

    #[Test]
    public function it_converts_to_array_for_model()
    {
        // Arrange
        $shopData = new ShopData(
            shopifyId: 123456,
            name: 'Test Store',
            email: 'test@example.com',
            domain: 'test-store.myshopify.com',
            shopifyDomain: 'test-store.myshopify.com',
            currency: 'USD',
            timezone: 'America/New_York',
            accessToken: 'test_token',
            scopes: ['read_products', 'write_products'],
            isActive: true
        );

        // Act
        $array = $shopData->toArray();

        // Assert
        $this->assertEquals(123456, $array['shopify_shop_id']);
        $this->assertEquals('Test Store', $array['name']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('test-store.myshopify.com', $array['domain']);
        $this->assertEquals('test-store.myshopify.com', $array['shopify_domain']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals('America/New_York', $array['timezone']);
        $this->assertEquals(['read_products', 'write_products'], $array['scopes']);
        $this->assertTrue($array['is_active']);

        $this->assertNotEquals('test_token', $array['access_token']);
        $this->assertIsString($array['access_token']);
    }
}
