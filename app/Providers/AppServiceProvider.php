<?php

namespace App\Providers;

use App\Domains\Shopify\Services\ShopifyAuthService;
use App\Domains\Shopify\Services\ShopifyProductService;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(ShopifyAuthService::class)
            ->needs('$apiKey')
            ->give(config('shopify.api_key'));

        $this->app->when(ShopifyAuthService::class)
            ->needs('$apiSecret')
            ->give(config('shopify.api_secret'));

        $this->app->when(ShopifyAuthService::class)
            ->needs(Client::class)
            ->give(function () {
                return new Client([
                    'timeout' => 30.0,
                    'verify' => $this->app->environment('production'),
                ]);
            });

        $this->app->bind(ShopifyProductService::class, function ($app) {
            return new ShopifyProductService(
                client: new Client([
                    'timeout' => 30.0,
                    'verify' => $app->environment('production'),
                ]),
                authService: $app->make(ShopifyAuthService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
