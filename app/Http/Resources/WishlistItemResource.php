<?php

namespace App\Http\Resources;

use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WishlistItem
 */
class WishlistItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wishlist_id' => $this->wishlist_id,
            'shopify_product_id' => $this->shopify_product_id,
            'shopify_variant_id' => $this->shopify_variant_id,
            'product_title' => $this->product_title,
            'variant_title' => $this->variant_title,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'quantity' => $this->quantity,
            'image_url' => $this->image_url,
            'handle' => $this->handle,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
