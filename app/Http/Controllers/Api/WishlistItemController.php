<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistItemResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request, Wishlist $wishlist): JsonResponse
    {
        $validated = $request->validate([
            'shopify_product_id' => ['required', 'string', 'max:255'],
            'shopify_variant_id' => ['nullable', 'string', 'max:255'],
            'product_title' => ['required', 'string', 'max:255'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['integer', 'min:1', 'max:100'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'handle' => ['nullable', 'string', 'max:255'],
        ]);

        $item = $wishlist->items()->create($validated);

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
    public function update(Request $request, Wishlist $wishlist, WishlistItem $item): WishlistItemResource
    {
        abort_unless($item->wishlist_id === $wishlist->id, 404);

        $validated = $request->validate([
            'product_title' => ['sometimes', 'required', 'string', 'max:255'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'handle' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $item->update($validated);

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
