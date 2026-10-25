<?php

namespace Tests\Feature\Shopify;

use App\Http\Middleware\VerifyShopifyWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShopifyMiddlewareTest extends TestCase
{
    public function test_it_passes_when_webhook_secret_is_not_set()
    {
        config(['shopify.webhook_secret' => null]);

        $middleware = new VerifyShopifyWebhook();
        $request = Request::create('/test', 'POST');
        
        $response = $middleware->handle($request, fn ($req) => new Response('OK'));
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_it_rejects_when_hmac_is_missing()
    {
        config(['shopify.webhook_secret' => 'test-secret']);

        $middleware = new VerifyShopifyWebhook();
        $request = Request::create('/test', 'POST');
        
        try {
            $middleware->handle($request, fn ($req) => new Response('OK'));
            $this->fail('Expected exception for missing HMAC');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(401, $e->getStatusCode());
            $this->assertStringContainsString('Missing HMAC signature', $e->getMessage());
        }
    }

    public function test_it_rejects_when_hmac_is_invalid()
    {
        config(['shopify.webhook_secret' => 'test-secret']);

        $middleware = new VerifyShopifyWebhook();
        
        // Create request with body content and HMAC header
        $request = new Request(
            [], // query
            [], // request
            [], // attributes
            [], // cookies
            [], // files
            [
                'HTTP_X_Shopify_Hmac_Sha256' => 'invalid-hmac',
                'REQUEST_METHOD' => 'POST',
            ],
            'test-body'
        );
        
        try {
            $middleware->handle($request, fn ($req) => new Response('OK'));
            $this->fail('Expected exception for invalid HMAC');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(401, $e->getStatusCode());
            $this->assertStringContainsString('Invalid HMAC signature', $e->getMessage());
        }
    }

    public function test_it_passes_when_hmac_is_valid()
    {
        config(['shopify.webhook_secret' => 'test-secret']);

        $middleware = new VerifyShopifyWebhook();
        
        $body = 'test-body';
        $hmac = base64_encode(hash_hmac('sha256', $body, 'test-secret', true));
        
        // Create request with body content and valid HMAC header
        $request = new Request(
            [], // query
            [], // request
            [], // attributes
            [], // cookies
            [], // files
            [
                'HTTP_X_Shopify_Hmac_Sha256' => $hmac,
                'REQUEST_METHOD' => 'POST',
            ],
            $body
        );
        
        $response = $middleware->handle($request, fn ($req) => new Response('OK'));
        
        $this->assertEquals('OK', $response->getContent());
    }
}
