<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistItemRequest;
use App\Http\Requests\UpdateWishlistItemRequest;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WishlistItemController extends Controller
{
    /**
     * List all items in a wishlist.
     */
    public function index(Wishlist $wishlist): JsonResponse
    {
        $items = $wishlist->items()->get();

        return response()->json($items);
    }

    /**
     * Add an item to a wishlist.
     */
    public function store(Wishlist $wishlist, StoreWishlistItemRequest $request): JsonResponse
    {
        $item = $wishlist->items()->create($request->validated());

        return response()->json($item, Response::HTTP_CREATED);
    }

    /**
     * Update a wishlist item (quantity).
     */
    public function update(Wishlist $wishlist, WishlistItem $item, UpdateWishlistItemRequest $request): JsonResponse
    {
        $item->update($request->validated());

        return response()->json($item);
    }

    /**
     * Remove an item from a wishlist.
     */
    public function destroy(Wishlist $wishlist, WishlistItem $item): JsonResponse
    {
        $item->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
