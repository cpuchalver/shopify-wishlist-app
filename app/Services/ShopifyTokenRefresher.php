<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShopifyTokenRefresher
{
    /**
     * Refresh the shop's expiring offline access token using its
     * refresh token. Rotation applies: both the access token and the
     * refresh token are replaced with the values from the response.
     *
     * @throws RuntimeException when the refresh fails
     */
    public function refresh(Shop $shop): void
    {
        if (blank($shop->refresh_token)) {
            throw new RuntimeException("Shop {$shop->domain} has no refresh token; re-authentication required.");
        }

        $lock = Cache::lock("shopify_token_refresh:{$shop->id}", 30);

        $lock->block(10, function () use ($shop): void {
            $shop->refresh();

            $response = Http::asJson()->post(
                "https://{$shop->domain}/admin/oauth/access_token",
                [
                    'client_id' => config('shopify.api_key'),
                    'client_secret' => config('shopify.api_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $shop->refresh_token,
                ],
            );

            if ($response->failed() || blank($response->json('access_token'))) {
                report("Shopify token refresh failed for {$shop->domain}: " . $response->status() . ' ' . (string) $response->body());

                throw new RuntimeException("Shop {$shop->domain} token refresh failed ({$response->status()}).");
            }

            $shop->update([
                'access_token' => $response->json('access_token'),
                'refresh_token' => $response->json('refresh_token'),
                'token_expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
            ]);
        });
    }

    /**
     * Refresh the token only when it is missing or about to expire.
     */
    public function refreshIfExpiring(Shop $shop, int $marginSeconds = 300): void
    {
        if ($shop->token_expires_at !== null && $shop->token_expires_at->isFuture() && $shop->token_expires_at->diffInRealSeconds(now()) > $marginSeconds) {
            return;
        }

        $this->refresh($shop);
    }
}
