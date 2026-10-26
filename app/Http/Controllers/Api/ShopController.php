<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopController extends Controller
{
    /**
     * Display a paginated listing of shops.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $shops = Shop::query()
            ->when(
                $request->query('status'),
                fn (Builder $query, string $status) => $query->where('status', $status),
            )
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ShopResource::collection($shops);
    }

    /**
     * Store a newly created shop.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'shopify_shop_id' => ['required', 'string', 'max:255'],
            'access_token' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'installed_at' => ['nullable', 'date'],
        ]);

        $shop = Shop::create($validated);

        return ShopResource::make($shop)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified shop.
     */
    public function show(Shop $shop): ShopResource
    {
        return ShopResource::make($shop);
    }

    /**
     * Update the specified shop.
     */
    public function update(Request $request, Shop $shop): ShopResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'domain' => ['sometimes', 'required', 'string', 'max:255'],
            'access_token' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $shop->update($validated);

        return ShopResource::make($shop->refresh());
    }

    /**
     * Remove the specified shop.
     */
    public function destroy(Shop $shop): JsonResponse
    {
        $shop->delete();

        return response()->json(status: 204);
    }
}
