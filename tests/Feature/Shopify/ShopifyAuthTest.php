<?php

namespace Tests\Feature\Shopify;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopifyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_to_shopify_oauth()
    {
        $shopDomain = 'test-store.myshopify.com';

        $response = $this->get("/shopify/auth?shop={$shopDomain}");

        $response->assertStatus(302);
        $response->assertRedirectContains('https://' . $shopDomain . '/admin/oauth/authorize');
        $response->assertRedirectContains('client_id=' . config('shopify.api_key'));
        $response->assertRedirectContains('scope=' . urlencode(config('shopify.scopes')));
        $response->assertRedirectContains('redirect_uri=' . urlencode(config('shopify.redirect_uri')));
    }

    public function test_it_requires_shop_parameter()
    {
        $response = $this->get('/shopify/auth');

        $response->assertStatus(302);
    }

    public function test_it_handles_oauth_callback_with_valid_code()
    {
        $shopDomain = 'test-store.myshopify.com';
        $accessToken = 'test-access-token';

        // Mock the HTTP client to return a successful token exchange
        Http::fake([
            "https://{$shopDomain}/admin/oauth/access_token" => Http::response([
                'access_token' => $accessToken,
            ]),
            "https://{$shopDomain}/admin/api/" . config('shopify.api_version') . "/shop.json" => Http::response([
                'shop' => [
                    'name' => 'Test Store',
                    'id' => 'gid://shopify/Shop/123456789',
                ],
            ]),
        ]);

        // Calculate HMAC for verification
        $hmac = hash_hmac('sha256', http_build_query([
            'shop' => $shopDomain,
            'code' => 'test-code',
            'state' => 'test-nonce',
        ]), config('shopify.api_secret_key'));

        $response = $this->withSession(['shop' => $shopDomain, 'nonce' => 'test-nonce'])
            ->get("/shopify/auth/callback?shop={$shopDomain}&code=test-code&state=test-nonce&hmac={$hmac}");

        $response->assertRedirect();
        
        // Verify shop was created
        $this->assertDatabaseHas('shops', [
            'domain' => $shopDomain,
            'access_token' => $accessToken,
            'status' => 'active',
        ]);
    }

    public function test_it_rejects_callback_with_invalid_hmac()
    {
        $response = $this->get('/shopify/auth/callback?shop=test.myshopify.com&code=test&hmac=invalid');

        $response->assertRedirect();
    }

    public function test_it_rejects_callback_with_shop_mismatch()
    {
        // Calculate HMAC for different shop
        $hmac = hash_hmac('sha256', http_build_query([
            'shop' => 'callback-shop.myshopify.com',
            'code' => 'test-code',
            'state' => 'test-nonce',
        ]), config('shopify.api_secret_key'));

        $response = $this->withSession(['shop' => 'stored-shop.myshopify.com', 'nonce' => 'test-nonce'])
            ->get("/shopify/auth/callback?shop=callback-shop.myshopify.com&code=test-code&state=test-nonce&hmac={$hmac}");

        $response->assertRedirect();
    }
}
