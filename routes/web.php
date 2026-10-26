<?php

use App\Http\Controllers\Auth\ShopifyInstallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/shopify/auth/redirect', [ShopifyInstallController::class, 'redirect'])
    ->name('shopify.auth.redirect');

Route::get('/shopify/auth/callback', [ShopifyInstallController::class, 'callback'])
    ->name('shopify.auth.callback');
