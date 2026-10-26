<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistItemRequest;
use App\Http\Requests\UpdateWishlistItemRequest;
use App\Http\Resources\WishlistItemResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistItemController extends Controller
{
    /**
     * Display a listing of the wishlist items.
     */
    public function index(Wishlist $wishlist): AnonymousResourceCollection
    {
        return WishlistItemResource::collection(
            $wishlist->items()->latest()->get(),
        );
    }

    /**
     * Store a newly created wishlist item.
     */
    public function store(StoreWishlistItemRequest $request, Wishlist $wishlist): JsonResponse
    {
        $item = $wishlist->items()->create($request->validated());

        return WishlistItemResource::make($item)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified wishlist item.
     */
    public function show(Wishlist $wishlist, WishlistItem $item): WishlistItemResource
    {
        abort_unless($item->wishlist_id === $wishlist->id, 404);

        return WishlistItemResource::make($item);
    }

    /**
     * Update the specified wishlist item.
     */
    public function update(UpdateWishlistItemRequest $request, Wishlist $wishlist, WishlistItem $item): WishlistItemResource
    {
        abort_unless($item->wishlist_id === $wishlist->id, 404);

        $item->update($request->validated());

        return WishlistItemResource::make($item->refresh());
    }

    /**
     * Remove the specified wishlist item.
     */
    public function destroy(Wishlist $wishlist, WishlistItem $item): JsonResponse
    {
        abort_unless($item->wishlist_id === $wishlist->id, 404);

        $item->delete();

        return response()->json(status: 204);
    }
}
