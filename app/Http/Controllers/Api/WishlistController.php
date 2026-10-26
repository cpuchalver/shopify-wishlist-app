<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends Controller
{
    /**
     * Display a paginated listing of wishlists.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $wishlists = Wishlist::query()
            ->with('items')
            ->when(
                $request->query('shop_id'),
                fn (Builder $query, string $shopId) => $query->where('shop_id', $shopId),
            )
            ->when(
                $request->query('customer_id'),
                fn (Builder $query, string $customerId) => $query->where('customer_id', $customerId),
            )
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return WishlistResource::collection($wishlists);
    }

    /**
     * Store a newly created wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shop_id' => ['required', 'uuid', 'exists:shops,id'],
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ]);

        $wishlist = Wishlist::create($validated);

        return WishlistResource::make($wishlist->load('items'))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified wishlist.
     */
    public function show(Wishlist $wishlist): WishlistResource
    {
        return WishlistResource::make($wishlist->load('items', 'customer'));
    }

    /**
     * Update the specified wishlist.
     */
    public function update(Request $request, Wishlist $wishlist): WishlistResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $wishlist->update($validated);

        return WishlistResource::make($wishlist->load('items'));
    }

    /**
     * Remove the specified wishlist.
     */
    public function destroy(Wishlist $wishlist): JsonResponse
    {
        $wishlist->delete();

        return response()->json(status: 204);
    }
}
