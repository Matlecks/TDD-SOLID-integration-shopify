<?php

use App\Http\Controllers\Api\V1\ProductsController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductsController::class, 'index'])->name('products.index');
    Route::post('/', [ProductsController::class, 'store'])->name('products.store');
    Route::get('/{product}', [ProductsController::class, 'show'])->name('products.show');
    Route::put('/{product}', [ProductsController::class, 'update'])->name('products.update');
    Route::delete('/{product}', [ProductsController::class, 'destroy'])->name('products.destroy');
});
