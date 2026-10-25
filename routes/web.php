<?php

use Illuminate\Support\Facades\Route;

// Shopify routes
require __DIR__ . '/shopify.php';

Route::get('/', function () {
    return view('welcome');
});
