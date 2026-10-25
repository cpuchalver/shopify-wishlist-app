<?php

namespace Database\Factories;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WishlistItem>
 */
class WishlistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productTitle = Str::random(15);
        $variantTitle = rand(0, 1) ? Str::random(10) . ' Variant' : null;

        return [
            'wishlist_id' => Wishlist::factory(),
            'shopify_product_id' => 'gid://shopify/Product/' . Str::uuid(),
            'shopify_variant_id' => null,
            'product_title' => $productTitle,
            'variant_title' => null,
            'price' => 19.99,
            'compare_at_price' => null,
            'quantity' => 1,
            'image_url' => null,
            'handle' => strtolower(str_replace(' ', '-', $productTitle)) . '-' . rand(10000, 99999),
        ];
    }

    /**
     * Create an item without a variant.
     */
    public function withoutVariant(): static
    {
        return $this->state(fn (array $attributes) => [
            'shopify_variant_id' => null,
            'variant_title' => null,
        ]);
    }

    /**
     * Create an item with a specific quantity.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create an item for a specific wishlist.
     */
    public function forWishlist(Wishlist $wishlist): static
    {
        return $this->state(fn (array $attributes) => [
            'wishlist_id' => $wishlist->id,
        ]);
    }
}
