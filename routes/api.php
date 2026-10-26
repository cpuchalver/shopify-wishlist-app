<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\WishlistItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('shops', ShopController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('wishlists', WishlistController::class);
Route::apiResource('wishlists.items', WishlistItemController::class)->shallow();
