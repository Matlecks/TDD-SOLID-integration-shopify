<?php

namespace App\Domains\Shopify\Providers;

use App\Domains\Shopify\Console\Commands\FetchShopifyShopData;
use App\Domains\Shopify\Console\Commands\SyncShopifyProducts;
use Illuminate\Support\ServiceProvider;

class ShopifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchShopifyShopData::class,
                SyncShopifyProducts::class,
            ]);
        }
    }
}
