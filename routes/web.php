<?php

use App\Domains\Shopify\Actions\HandleShopifyCallbackAction;
use App\Domains\Shopify\Actions\InstallShopifyAppAction;
use App\Domains\Shopify\Actions\ShowDashboardPageAction;
use App\Domains\Shopify\Actions\ShowInstallPageAction;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDashboardPageAction::class)
    ->name('home');

Route::get('/products/index', ShowDashboardPageAction::class)
    ->name('products.index');

Route::get('/install', ShowInstallPageAction::class)
    ->name('shopify.install.page');

Route::get('/auth/install', InstallShopifyAppAction::class)
    ->name('shopify.install');

// Маршрут для OAuth callback
Route::get('/auth/callback', HandleShopifyCallbackAction::class)
    ->name('shopify.callback');
