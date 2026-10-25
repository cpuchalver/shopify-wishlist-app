<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyAuthController extends Controller
{
    /**
     * The Shopify API key.
     */
    protected string $apiKey;

    /**
     * The Shopify API secret key.
     */
    protected string $apiSecretKey;

    /**
     * The Shopify API version.
     */
    protected string $apiVersion;

    /**
     * The Shopify scopes.
     */
    protected string $scopes;

    /**
     * The redirect URI.
     */
    protected string $redirectUri;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->apiKey = config('shopify.api_key');
        $this->apiSecretKey = config('shopify.api_secret_key');
        $this->apiVersion = config('shopify.api_version');
        $this->scopes = config('shopify.scopes');
        $this->redirectUri = config('shopify.redirect_uri');
    }

    /**
     * Redirect to Shopify for OAuth authorization.
     */
    public function redirectToShopify(Request $request): RedirectResponse
    {
        $shop = $request->query('shop');

        if (empty($shop)) {
            return redirect()->back()->with('error', 'Shop parameter is required');
        }

        // Store the shop domain in session for callback verification
        $request->session()->put('shop', $shop);

        // Generate a nonce for security
        $nonce = Str::random(32);
        $request->session()->put('nonce', $nonce);

        $installUrl = $this->buildInstallUrl($shop, $nonce);

        return redirect($installUrl);
    }

    /**
     * Handle the OAuth callback from Shopify.
     */
    public function callback(Request $request): RedirectResponse
    {
        $shop = $request->query('shop');
        $code = $request->query('code');
        $hmac = $request->query('hmac');
        $state = $request->query('state');

        // Verify HMAC signature
        if (!$this->verifyHmac($request->query())) {
            return redirect('/')->with('error', 'Invalid HMAC signature');
        }

        // Verify the shop domain matches what we stored
        $storedShop = $request->session()->get('shop');
        if ($shop !== $storedShop) {
            return redirect('/')->with('error', 'Shop domain mismatch');
        }

        // Exchange the authorization code for an access token
        $accessToken = $this->exchangeCodeForToken($shop, $code);

        if (!$accessToken) {
            return redirect('/')->with('error', 'Failed to get access token');
        }

        // Store or update the shop information
        $shopModel = Shop::updateOrCreate(
            ['domain' => $shop],
            [
                'name' => $this->getShopName($shop, $accessToken),
                'shopify_shop_id' => $this->getShopId($shop, $accessToken),
                'access_token' => $accessToken,
                'status' => 'active',
                'installed_at' => now(),
            ]
        );

        // Clean up session
        $request->session()->forget('shop');
        $request->session()->forget('nonce');

        return redirect('/')->with('success', 'Shop successfully installed');
    }

    /**
     * Handle app uninstallation webhook.
     */
    public function uninstall(Request $request): JsonResponse
    {
        $shop = $request->header('X-Shopify-Shop-Domain');
        $hmac = $request->header('X-Shopify-Hmac-Sha256');

        // Verify webhook signature
        if (!$this->verifyWebhook($request->getContent(), $hmac)) {
            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }

        // Find and mark shop as uninstalled
        $shopModel = Shop::where('domain', $shop)->first();

        if ($shopModel) {
            $shopModel->update(['status' => 'uninstalled']);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Shopify webhooks.
     */
    public function webhook(Request $request): JsonResponse
    {
        $topic = $request->header('X-Shopify-Topic');
        $shop = $request->header('X-Shopify-Shop-Domain');
        $hmac = $request->header('X-Shopify-Hmac-Sha256');

        // Verify webhook signature
        if (!$this->verifyWebhook($request->getContent(), $hmac)) {
            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }

        // Handle different webhook topics
        switch ($topic) {
            case 'app/uninstalled':
                return $this->uninstall($request);

            case 'customers/create':
            case 'customers/update':
                // Handle customer events
                Log::info("Received webhook: {$topic} for shop: {$shop}");
                break;

            default:
                Log::info("Received unhandled webhook: {$topic} for shop: {$shop}");
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Build the Shopify install URL.
     */
    protected function buildInstallUrl(string $shop, string $nonce): string
    {
        $scopes = urlencode($this->scopes);
        $redirectUri = urlencode($this->redirectUri);

        return "https://{$shop}/admin/oauth/authorize" .
            "?client_id={$this->apiKey}" .
            "&scope={$scopes}" .
            "&redirect_uri={$redirectUri}" .
            "&state={$nonce}";
    }

    /**
     * Verify the HMAC signature from Shopify.
     */
    protected function verifyHmac(array $query): bool
    {
        if (!isset($query['hmac'])) {
            return false;
        }

        $hmac = $query['hmac'];
        unset($query['hmac']);

        $calculatedHmac = hash_hmac('sha256', http_build_query($query), $this->apiSecretKey);

        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Verify the webhook signature.
     */
    protected function verifyWebhook(string $body, ?string $hmac): bool
    {
        if (!$hmac) {
            return false;
        }

        $webhookSecret = config('shopify.webhook_secret');
        if (!$webhookSecret) {
            return true; // Skip verification if no secret is set (for testing)
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $body, $webhookSecret, true));

        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Exchange the authorization code for an access token.
     */
    protected function exchangeCodeForToken(string $shop, string $code): ?string
    {
        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecretKey,
            'code' => $code,
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'] ?? null;
        }

        Log::error('Failed to exchange code for token', [
            'shop' => $shop,
            'response' => $response->body(),
        ]);

        return null;
    }

    /**
     * Get the shop name from Shopify.
     */
    protected function getShopName(string $shop, string $accessToken): string
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Accept' => 'application/json',
        ])->get("https://{$shop}/admin/api/{$this->apiVersion}/shop.json");

        return $response->json()['shop']['name'] ?? $shop;
    }

    /**
     * Get the shop ID from Shopify.
     */
    protected function getShopId(string $shop, string $accessToken): string
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Accept' => 'application/json',
        ])->get("https://{$shop}/admin/api/{$this->apiVersion}/shop.json");

        return $response->json()['shop']['id'] ?? "gid://shopify/Shop/{$shop}";
    }
}
