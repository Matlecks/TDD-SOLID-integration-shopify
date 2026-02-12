<?php

namespace App\Domains\Shopify\Console\Commands;

use App\Domains\Shopify\Jobs\SyncProductsJob;
use App\Models\ShopifyShop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncShopifyProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all products from all Shopify stores via queue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting products sync for all Shopify stores...');

        $shops = ShopifyShop::all();

        if ($shops->isEmpty()) {
            $this->warn('No Shopify stores found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$shops->count()} stores. Dispatching jobs to queue...");

        $bar = $this->output->createProgressBar($shops->count());
        $bar->start();

        foreach ($shops as $shop) {
            try {
                // Диспатчим первый джоб для каждого магазина
                SyncProductsJob::dispatch($shop, 1, null, 50);

                Log::info('Dispatched SyncProductsJob for shop', [
                    'shop_id' => $shop->id,
                    'shop_domain' => $shop->domain
                ]);

                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nFailed to dispatch job for shop {$shop->domain}: {$e->getMessage()}");

                Log::error('Failed to dispatch SyncProductsJob', [
                    'shop_id' => $shop->id,
                    'shop_domain' => $shop->domain,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('All products sync jobs have been dispatched to the queue!');

        return Command::SUCCESS;
    }
}
