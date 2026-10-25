<?php

use App\Http\Controllers\ShopifyAuthController;
use Illuminate\Support\Facades\Route;

// Shopify OAuth routes
Route::prefix('shopify')->group(function () {
    // OAuth authorization
    Route::get('auth', [ShopifyAuthController::class, 'redirectToShopify']);
    
    // OAuth callback
    Route::get('auth/callback', [ShopifyAuthController::class, 'callback']);
    
    // Webhooks with verification middleware
    Route::middleware('shopify.webhook')->group(function () {
        Route::post('webhooks', [ShopifyAuthController::class, 'webhook']);
        Route::post('uninstall', [ShopifyAuthController::class, 'uninstall']);
    });
});
