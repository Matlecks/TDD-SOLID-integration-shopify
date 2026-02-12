<?php

namespace App\Domains\Shopify\Console;

use App\Domains\Shopify\Console\Commands\FetchShopifyShopData;
use App\Domains\Shopify\Console\Commands\SyncShopifyProducts;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class ConsoleKernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FetchShopifyShopData::class,
        SyncShopifyProducts::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(SyncShopifyProducts::class, ['--shop-id=1'])
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->name('shopify_products_sync_shop_1')
            ->onSuccess(function () {
                logger('Shopify products sync for shop 1 completed successfully');
            })
            ->onFailure(function () {
                logger('Shopify products sync for shop 1 failed');
            });

        $schedule->command(FetchShopifyShopData::class)
            ->everyTwoHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->name('shopify_fetch_shop_data')
            ->onSuccess(function () {
                logger('Shopify shop data fetch completed successfully');
            })
            ->onFailure(function () {
                logger('Shopify shop data fetch failed');
            })
            ->emailOutputOnFailure(config('shopify.admin_email'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
