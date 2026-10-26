<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
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
    public function store(StoreShopRequest $request): JsonResponse
    {
        $shop = Shop::create($request->validated());

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
    public function update(UpdateShopRequest $request, Shop $shop): ShopResource
    {
        $shop->update($request->validated());

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
