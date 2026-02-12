<?php

namespace App\Domains\Shopify\Actions;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShopifyShop;
use Illuminate\View\View;

class ShowDashboardPageAction extends Controller
{
    /**
     * Displays the dashboard page.
     *
     * @return View
     */
    public function __invoke(): View
    {
        $shop = ShopifyShop::first();

        if (!$shop) {
            return view('shopify.dashboard', [
                'recentProducts' => collect([]),
                'shop' => null,
            ]);
        }

        $recentProducts = Product::where('shopify_shop_id', $shop->id)
            ->latest()
            ->take(5)
            ->get();

        return view('shopify.dashboard', [
            'recentProducts' => $recentProducts,
            'shop' => $shop,
        ]);
    }
}
