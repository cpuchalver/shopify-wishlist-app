<?php

use App\Models\Shop;
use App\Services\ShopifyTokenRefresher;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['shopify.api_key' => 'test-api-key']);
    config(['shopify.api_secret' => 'test-api-secret']);
});

test('refresh rotates the access token and the refresh token', function () {
    $shop = Shop::factory()->create([
        'refresh_token' => 'shprt-old-token',
        'token_expires_at' => now()->subMinute(),
    ]);

    Http::fake([
        "{$shop->domain}/admin/oauth/access_token" => Http::response([
            'access_token' => 'shpat-new-token',
            'refresh_token' => 'shprt-new-token',
            'expires_in' => 3600,
        ]),
    ]);

    app(ShopifyTokenRefresher::class)->refresh($shop);

    expect($shop->refresh()->access_token)->toBe('shpat-new-token')
        ->and($shop->refresh()->refresh_token)->toBe('shprt-new-token')
        ->and($shop->refresh()->token_expires_at)->toBeInstanceOf(\Carbon\Carbon::class)
        ->and($shop->refresh()->token_expires_at->isFuture())->toBeTrue();
});

test('refresh throws when the shop has no refresh token', function () {
    $shop = Shop::factory()->create([
        'refresh_token' => null,
    ]);

    app(ShopifyTokenRefresher::class)->refresh($shop);
})->throws(RuntimeException::class);

test('refresh throws when shopify rejects the refresh token', function () {
    $shop = Shop::factory()->create([
        'refresh_token' => 'shprt-revoked',
        'token_expires_at' => now()->subMinute(),
    ]);

    Http::fake([
        "{$shop->domain}/admin/oauth/access_token" => Http::response([
            'error' => 'invalid_request',
        ], 401),
    ]);

    app(ShopifyTokenRefresher::class)->refresh($shop);
})->throws(RuntimeException::class);

test('refresh skips a token that is still valid', function () {
    $shop = Shop::factory()->create([
        'refresh_token' => 'shprt-valid',
        'token_expires_at' => now()->addMinutes(30),
    ]);

    Http::fake();

    app(ShopifyTokenRefresher::class)->refreshIfExpiring($shop);

    Http::assertNothingSent();
});

test('refresh refreshes a token that expires within the margin', function () {
    $shop = Shop::factory()->create([
        'refresh_token' => 'shprt-nearly-expired',
        'token_expires_at' => now()->addMinute(),
    ]);

    Http::fake([
        "{$shop->domain}/admin/oauth/access_token" => Http::response([
            'access_token' => 'shpat-new-token',
            'refresh_token' => 'shprt-new-token',
            'expires_in' => 3600,
        ]),
    ]);

    app(ShopifyTokenRefresher::class)->refreshIfExpiring($shop);

    expect($shop->refresh()->access_token)->toBe('shpat-new-token');
});

test('the scheduled command refreshes shops with expiring tokens', function () {
    $expiring = Shop::factory()->create([
        'refresh_token' => 'shprt-expiring',
        'token_expires_at' => now()->addMinutes(5),
    ]);
    $stillValid = Shop::factory()->create([
        'refresh_token' => 'shprt-valid',
        'token_expires_at' => now()->addMinutes(30),
    ]);

    Http::fake([
        "{$expiring->domain}/admin/oauth/access_token" => Http::response([
            'access_token' => 'shpat-new-token',
            'refresh_token' => 'shprt-new-token',
            'expires_in' => 3600,
        ]),
    ]);

    $this->artisan('shopify:refresh-tokens')
        ->expectsOutputToContain('Refreshed 1 token(s)')
        ->assertSuccessful();

    expect($expiring->refresh()->access_token)->toBe('shpat-new-token')
        ->and($stillValid->refresh()->access_token)->not->toBe('shpat-new-token');
});

test('the scheduled command fails when a refresh fails', function () {
    $shop = Shop::factory()->create([
        'refresh_token' => 'shprt-broken',
        'token_expires_at' => now()->subMinute(),
    ]);

    Http::fake([
        "{$shop->domain}/admin/oauth/access_token" => Http::response([
            'error' => 'invalid_request',
        ], 401),
    ]);

    $this->artisan('shopify:refresh-tokens')->assertFailed();
});
