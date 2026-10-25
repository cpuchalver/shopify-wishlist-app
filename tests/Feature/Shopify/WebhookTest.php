<?php

namespace Tests\Feature\Shopify;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_handles_app_uninstalled_webhook()
    {
        $shop = Shop::factory()->create([
            'domain' => 'test-store.myshopify.com',
            'status' => 'active',
        ]);

        // Provide valid HMAC - body is empty for uninstall
        $body = '';
        $hmac = base64_encode(hash_hmac('sha256', $body, 'test_webhook_secret', true));

        $response = $this->withHeaders([
            'X-Shopify-Shop-Domain' => 'test-store.myshopify.com',
            'X-Shopify-Hmac-Sha256' => $hmac,
            'Content-Type' => 'application/json',
        ])->post('/shopify/uninstall', []);

        $response->assertStatus(200);
        
        // Verify shop was marked as uninstalled
        $this->assertDatabaseHas('shops', [
            'domain' => 'test-store.myshopify.com',
            'status' => 'uninstalled',
        ]);
    }

    public function test_it_rejects_webhook_with_invalid_hmac()
    {
        // Body is empty for webhooks with no data
        $body = '';
        $hmac = base64_encode(hash_hmac('sha256', $body, 'wrong-secret', true));

        $response = $this->withHeaders([
            'X-Shopify-Shop-Domain' => 'test-store.myshopify.com',
            'X-Shopify-Hmac-Sha256' => $hmac,
            'Content-Type' => 'application/json',
        ])->post('/shopify/webhooks', []);

        $response->assertStatus(401);
    }

    public function test_it_accepts_webhook_with_valid_hmac()
    {
        // Body is empty for webhooks with no data
        $body = '';
        $hmac = base64_encode(hash_hmac('sha256', $body, 'test_webhook_secret', true));

        $response = $this->withHeaders([
            'X-Shopify-Shop-Domain' => 'test-store.myshopify.com',
            'X-Shopify-Hmac-Sha256' => $hmac,
            'Content-Type' => 'application/json',
        ])->post('/shopify/webhooks', []);

        $response->assertStatus(200);
    }

    public function test_it_handles_customer_webhooks()
    {
        // For POST requests with data, the body is the JSON-encoded array
        $body = json_encode(['id' => 123]);
        $hmac = base64_encode(hash_hmac('sha256', $body, 'test_webhook_secret', true));

        $response = $this->withHeaders([
            'X-Shopify-Shop-Domain' => 'test-store.myshopify.com',
            'X-Shopify-Topic' => 'customers/create',
            'X-Shopify-Hmac-Sha256' => $hmac,
            'Content-Type' => 'application/json',
        ])->postJson('/shopify/webhooks', ['id' => 123]);

        $response->assertStatus(200);
    }
}
