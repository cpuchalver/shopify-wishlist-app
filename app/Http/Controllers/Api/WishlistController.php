<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistRequest;
use App\Models\Shop;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WishlistController extends Controller
{
    /**
     * List all wishlists for a shop.
     */
    public function index(Shop $shop): AnonymousResourceCollection
    {
        $wishlists = $shop->wishlists()->with(['customer', 'items'])->get();

        return response()->json($wishlists);
    }

    /**
     * Create a new wishlist for a shop.
     */
    public function store(Shop $shop, StoreWishlistRequest $request): JsonResponse
    {
        $wishlist = $shop->wishlists()->create($request->validated());

        return response()->json($wishlist, Response::HTTP_CREATED);
    }

    /**
     * Display a specific wishlist.
     */
    public function show(Wishlist $wishlist): JsonResponse
    {
        return response()->json($wishlist->load(['shop', 'customer', 'items']));
    }

    /**
     * Delete a wishlist.
     */
    public function destroy(Wishlist $wishlist): JsonResponse
    {
        $wishlist->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
