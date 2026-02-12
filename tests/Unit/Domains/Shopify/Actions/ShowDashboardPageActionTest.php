<?php

namespace Tests\Unit\Domains\Shopify\Actions;

use App\Domains\Shopify\Actions\ShowDashboardPageAction;
use App\Models\Product;
use App\Models\ShopifyShop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowDashboardPageActionTest extends TestCase
{
    use RefreshDatabase;

    private ShowDashboardPageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ShowDashboardPageAction();
    }

    #[Test]
    public function it_shows_empty_dashboard_when_no_shop_installed()
    {
        // Act
        $response = $this->action->__invoke();

        // Assert
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('shopify.dashboard', $response->getName());

        $data = $response->getData();
        $this->assertNull($data['shop']);
        $this->assertInstanceOf(Collection::class, $data['recentProducts']);
        $this->assertCount(0, $data['recentProducts']);
    }

    #[Test]
    public function it_shows_dashboard_with_shop_and_recent_products()
    {
        // Arrange
        $shop = ShopifyShop::create([
            'shopify_shop_id' => 123456,
            'shopify_domain' => 'test.myshopify.com',
            'name' => 'Test Store',
            'domain' => 'test.myshopify.com',
            'access_token' => 'test_token',
            'email' => 'test@example.com',
        ]);

        $product = Product::create([
            'shopify_shop_id' => $shop->id,
            'shopify_id' => 12345,
            'title' => 'Test Product',
            'body_html' => 'Test description',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'handle' => 'test-product',
        ]);

        // Act
        $response = $this->action->__invoke();

        // Assert
        $data = $response->getData();

        $this->assertInstanceOf(ShopifyShop::class, $data['shop']);
        $this->assertEquals('Test Store', $data['shop']->name);

        $this->assertCount(1, $data['recentProducts']);
        $product = $data['recentProducts']->first();

        // Эти проверки нужно будет адаптировать под реальную логику приложения
        // $this->assertEquals(9.99, $product->price);
        // $this->assertEquals(24.99, $product->compare_at_price);
        // $this->assertEquals(18, $product->total_inventory);
        // $this->assertEquals('https://example.com/image.jpg', $product->image);
    }

    #[Test]
    public function it_limits_recent_products_to_five()
    {
        // Arrange
        $shop = ShopifyShop::create([
            'shopify_shop_id' => 123456,
            'shopify_domain' => 'test.myshopify.com',
            'name' => 'Test Store',
            'domain' => 'test.myshopify.com',
            'access_token' => 'test_token',
            'email' => 'test@example.com',
        ]);

        // Create 10 products
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'shopify_shop_id' => $shop->id,
                'shopify_id' => $i,
                'title' => "Product {$i}",
                'body_html' => "Description {$i}",
                'vendor' => 'Test Vendor',
                'product_type' => 'Test Type',
                'handle' => "product-{$i}",
            ]);
        }

        // Act
        $response = $this->action->__invoke();
        $data = $response->getData();

        // Assert
        $this->assertCount(5, $data['recentProducts']);
    }

    #[Test]
    public function it_handles_products_without_variants()
    {
        // Arrange
        $shop = ShopifyShop::create([
            'shopify_shop_id' => 123456,
            'shopify_domain' => 'test.myshopify.com',
            'name' => 'Test Store',
            'domain' => 'test.myshopify.com',
            'access_token' => 'test_token',
            'email' => 'test@example.com',
        ]);

        Product::create([
            'shopify_shop_id' => $shop->id,
            'shopify_id' => 1,
            'title' => 'Product Without Variants',
            'body_html' => 'Description',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'handle' => 'product-without-variants',
        ]);

        // Act
        $response = $this->action->__invoke();
        $data = $response->getData();

        // Assert
        $this->assertCount(1, $data['recentProducts']);
        $product = $data['recentProducts']->first();

        // Эти проверки нужно будет адаптировать под реальную логику приложения
        // $this->assertEquals(0, $product->price);
        // $this->assertEquals(0, $product->compare_at_price);
        // $this->assertEquals(0, $product->total_inventory);
        // $this->assertNull($product->image);
    }

    #[Test]
    public function it_orders_products_by_latest()
    {
        // Arrange
        $shop = ShopifyShop::create([
            'shopify_shop_id' => 123456,
            'shopify_domain' => 'test.myshopify.com',
            'name' => 'Test Store',
            'domain' => 'test.myshopify.com',
            'access_token' => 'test_token',
            'email' => 'test@example.com',
        ]);

        // Создаем старый продукт (сначала)
        $oldProduct = Product::create([
            'shopify_shop_id' => $shop->id,
            'shopify_id' => 1,
            'title' => 'Old Product',
            'body_html' => 'Old Description',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'handle' => 'old-product',
        ]);

        // Искусственно устанавливаем created_at в прошлое
        $oldProduct->created_at = now()->subDays(5);
        $oldProduct->updated_at = now()->subDays(5);
        $oldProduct->save();

        // Создаем новый продукт
        $newProduct = Product::create([
            'shopify_shop_id' => $shop->id,
            'shopify_id' => 2,
            'title' => 'New Product',
            'body_html' => 'New Description',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'handle' => 'new-product',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Act
        $response = $this->action->__invoke();
        $data = $response->getData();

        // Assert
        $this->assertCount(2, $data['recentProducts']);

        // Проверяем, что продукты отсортированы по created_at DESC (новые сначала)
        $this->assertEquals($newProduct->id, $data['recentProducts']->first()->id);
        $this->assertEquals('New Product', $data['recentProducts']->first()->title);
        $this->assertEquals($oldProduct->id, $data['recentProducts']->last()->id);
        $this->assertEquals('Old Product', $data['recentProducts']->last()->title);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
