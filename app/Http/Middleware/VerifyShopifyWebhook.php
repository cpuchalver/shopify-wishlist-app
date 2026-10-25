<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifyWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $webhookSecret = config('shopify.webhook_secret');

        // Skip verification if no secret is set (for development)
        if (!$webhookSecret) {
            return $next($request);
        }

        if (!$hmac) {
            abort(401, 'Missing HMAC signature');
        }

        $body = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $body, $webhookSecret, true));

        if (!hash_equals($calculatedHmac, $hmac)) {
            abort(401, 'Invalid HMAC signature');
        }

        return $next($request);
    }
}
