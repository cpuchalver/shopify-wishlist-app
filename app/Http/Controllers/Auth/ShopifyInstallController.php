<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShopifyInstallController extends Controller
{
    /**
     * Redirect the merchant to the Shopify OAuth authorization screen.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop' => ['required', 'string', 'regex:/^[a-z0-9][a-z0-9\-]*\.myshopify\.com$/i'],
        ]);

        $shopDomain = strtolower($validated['shop']);
        $state = Str::uuid()->toString();

        $request->session()->put('shopify_oauth_state', $state);
        $request->session()->put('shopify_oauth_shop', $shopDomain);

        $authorizeUrl = sprintf(
            'https://%s/admin/oauth/authorize?%s',
            $shopDomain,
            http_build_query([
                'client_id' => config('shopify.api_key'),
                'scope' => config('shopify.scopes'),
                'redirect_uri' => route('shopify.auth.callback'),
                'state' => $state,
            ]),
        );

        return redirect()->away($authorizeUrl);
    }

    /**
     * Handle the OAuth callback: verify state and HMAC, exchange the
     * authorization code for an access token, and persist the shop.
     */
    public function callback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop' => ['required', 'string', 'regex:/^[a-z0-9][a-z0-9\-]*\.myshopify\.com$/i'],
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
            'hmac' => ['required', 'string'],
        ]);

        if (! hash_equals((string) $request->session()->pull('shopify_oauth_state'), $validated['state'])) {
            abort(403, 'Invalid OAuth state.');
        }

        if (! hash_equals((string) $request->session()->pull('shopify_oauth_shop'), strtolower($validated['shop']))) {
            abort(403, 'Shop mismatch.');
        }

        if (! $this->isValidHmac($request)) {
            abort(403, 'Invalid HMAC signature.');
        }

        $response = Http::asJson()->post(
            "https://{$validated['shop']}/admin/oauth/access_token",
            [
                'client_id' => config('shopify.api_key'),
                'client_secret' => config('shopify.api_secret'),
                'code' => $validated['code'],
            ],
        );

        if ($response->failed() || blank($response->json('access_token'))) {
            abort(502, 'Unable to retrieve the Shopify access token.');
        }

        $accessToken = $response->json('access_token');
        $shopDomain = strtolower($validated['shop']);

        $shopInfo = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])
            ->acceptJson()
            ->post("https://{$shopDomain}/admin/api/" . config('shopify.api_version') . '/graphql.json', [
                'query' => 'query { shop { id name email } }',
            ]);

        if ($shopInfo->failed() || blank($shopInfo->json('data.shop.id'))) {
            report("Shopify shop query failed for {$shopDomain}: " . $shopInfo->status() . ' ' . (string) $shopInfo->body());

            abort(502, 'Unable to retrieve the shop information (' . $shopInfo->status() . ').');
        }

        Shop::updateOrCreate(
            ['domain' => $shopDomain],
            [
                'name' => $shopInfo->json('data.shop.name') ?? $shopDomain,
                'shopify_shop_id' => $shopInfo->json('data.shop.id'),
                'email' => $shopInfo->json('data.shop.email'),
                'access_token' => $accessToken,
                'status' => 'active',
                'installed_at' => now(),
            ],
        );

        return redirect('/');
    }

    /**
     * Verify the HMAC signature of the callback query string.
     */
    private function isValidHmac(Request $request): bool
    {
        $params = $request->query();
        $providedHmac = $params['hmac'];
        unset($params['hmac']);

        ksort($params);
        $query = http_build_query($params);

        $computedHmac = hash_hmac('sha256', $query, config('shopify.api_secret'));

        return hash_equals($computedHmac, $providedHmac);
    }
}
