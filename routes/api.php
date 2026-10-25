<?php

use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\WishlistItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('shops/{shop}')->group(function () {
    // Wishlists endpoints
    Route::get('wishlists', [WishlistController::class, 'index']);
    Route::post('wishlists', [WishlistController::class, 'store']);
});

// Wishlists endpoints (without shop prefix for direct access)
Route::get('wishlists/{wishlist}', [WishlistController::class, 'show']);
Route::delete('wishlists/{wishlist}', [WishlistController::class, 'destroy']);

// Wishlist Items endpoints
Route::prefix('wishlists/{wishlist}')->group(function () {
    Route::get('items', [WishlistItemController::class, 'index']);
    Route::post('items', [WishlistItemController::class, 'store']);
});

// Wishlist Item update/delete endpoints
Route::patch('wishlists/{wishlist}/items/{item}', [WishlistItemController::class, 'update']);
Route::delete('wishlists/{wishlist}/items/{item}', [WishlistItemController::class, 'destroy']);
