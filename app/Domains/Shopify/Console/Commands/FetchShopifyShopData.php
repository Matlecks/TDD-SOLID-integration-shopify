<?php

namespace App\Domains\Shopify\Console\Commands;

use App\Domains\Shopify\Jobs\FetchShopifyShopDataJob;
use App\Models\ShopifyShop;
use Illuminate\Console\Command;

class FetchShopifyShopData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:fetch-shop-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Shopify shop data from API and update or create in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Shopify shop data fetch...');

        $shops = ShopifyShop::where('is_active', true)->get();

        if ($shops->isEmpty()) {
            $this->warn('No active Shopify shops found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$shops->count()} active shops in database. Dispatching jobs...");

        foreach ($shops as $shop) {
            FetchShopifyShopDataJob::dispatch($shop);
        }

        $this->info('All jobs dispatched successfully.');

        return Command::SUCCESS;
    }
}
