<?php

use App\Models\Shop;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['shopify.api_key' => 'test-api-key']);
    config(['shopify.api_secret' => 'test-api-secret']);
    config(['shopify.scopes' => 'read_products,write_customers']);
    config(['app.url' => 'https://wishlist-app.test']);
});

test('redirect sends the merchant to the shopify authorize url', function () {
    $response = $this->get('/shopify/auth/redirect?shop=test-shop.myshopify.com');

    $response->assertRedirect();

    $target = $response->headers->get('Location');
    expect($target)->toStartWith('https://test-shop.myshopify.com/admin/oauth/authorize?');

    parse_str(parse_url($target, PHP_URL_QUERY), $query);
    expect($query['client_id'])->toBe('test-api-key')
        ->and($query['scope'])->toBe('read_products,write_customers')
        ->and($query['redirect_uri'])->toBe('https://wishlist-app.test/shopify/auth/callback')
        ->and($query['state'])->toBeUuid();

    expect(session('shopify_oauth_state'))->toBe($query['state'])
        ->and(session('shopify_oauth_shop'))->toBe('test-shop.myshopify.com');
});

test('redirect rejects an invalid shop domain', function () {
    $response = $this->get('/shopify/auth/redirect?shop=not-a-shopify-domain.com');

    $response->assertSessionHasErrors('shop');
});

test('callback installs the shop with a valid hmac and state', function () {
    $this->get('/shopify/auth/redirect?shop=test-shop.myshopify.com');
    $state = session('shopify_oauth_state');

    $params = [
        'code' => 'valid-code',
        'shop' => 'test-shop.myshopify.com',
        'state' => $state,
        'timestamp' => (string) now()->getTimestamp(),
    ];
    $params['hmac'] = hash_hmac('sha256', http_build_query($params), 'test-api-secret');

    Http::fake([
        'test-shop.myshopify.com/admin/oauth/access_token' => Http::response([
            'access_token' => 'shpat-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ]),
    ]);

    $response = $this->get('/shopify/auth/callback?' . http_build_query($params));

    $response->assertRedirect('/');

    $shop = Shop::query()->where('domain', 'test-shop.myshopify.com')->first();
    expect($shop)->not->toBeNull()
        ->and($shop->access_token)->toBe('shpat-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
        ->and($shop->status)->toBe('active')
        ->and($shop->installed_at)->not->toBeNull();
});

test('callback rejects a tampered hmac', function () {
    $this->get('/shopify/auth/redirect?shop=test-shop.myshopify.com');
    $state = session('shopify_oauth_state');

    $params = [
        'code' => 'valid-code',
        'shop' => 'test-shop.myshopify.com',
        'state' => $state,
        'timestamp' => (string) now()->getTimestamp(),
        'hmac' => '0000000000000000000000000000000000000000000000000000000000000000',
    ];

    $response = $this->get('/shopify/auth/callback?' . http_build_query($params));

    $response->assertStatus(403);
});

test('callback rejects a mismatched state', function () {
    $this->get('/shopify/auth/redirect?shop=test-shop.myshopify.com');

    $params = [
        'code' => 'valid-code',
        'shop' => 'test-shop.myshopify.com',
        'state' => '00000000-0000-0000-0000-000000000000',
        'timestamp' => (string) now()->getTimestamp(),
    ];
    $params['hmac'] = hash_hmac('sha256', http_build_query($params), 'test-api-secret');

    $response = $this->get('/shopify/auth/callback?' . http_build_query($params));

    $response->assertStatus(403);
});

test('callback rejects a shop different from the one in session', function () {
    $this->get('/shopify/auth/redirect?shop=test-shop.myshopify.com');
    $state = session('shopify_oauth_state');

    $params = [
        'code' => 'valid-code',
        'shop' => 'other-shop.myshopify.com',
        'state' => $state,
        'timestamp' => (string) now()->getTimestamp(),
    ];
    $params['hmac'] = hash_hmac('sha256', http_build_query($params), 'test-api-secret');

    $response = $this->get('/shopify/auth/callback?' . http_build_query($params));

    $response->assertStatus(403);
});

test('callback aborts when the token exchange fails', function () {
    $this->get('/shopify/auth/redirect?shop=test-shop.myshopify.com');
    $state = session('shopify_oauth_state');

    $params = [
        'code' => 'invalid-code',
        'shop' => 'test-shop.myshopify.com',
        'state' => $state,
        'timestamp' => (string) now()->getTimestamp(),
    ];
    $params['hmac'] = hash_hmac('sha256', http_build_query($params), 'test-api-secret');

    Http::fake([
        'test-shop.myshopify.com/admin/oauth/access_token' => Http::response([
            'error' => 'invalid_code',
        ], 400),
    ]);

    $response = $this->get('/shopify/auth/callback?' . http_build_query($params));

    $response->assertStatus(502);
});
