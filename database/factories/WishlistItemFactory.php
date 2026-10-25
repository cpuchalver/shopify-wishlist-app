<?php

namespace Database\Factories;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $productTitle = $this->faker->word();
        $variantTitle = $this->faker->boolean() ? $this->faker->word() . ' Variant' : null;

        return [
            'wishlist_id' => Wishlist::factory(),
            'shopify_product_id' => 'gid://shopify/Product/' . $this->faker->uuid(),
            'shopify_variant_id' => 'gid://shopify/ProductVariant/' . $this->faker->uuid(),
            'product_title' => $productTitle,
            'variant_title' => $variantTitle,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'compare_at_price' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 20, 1500) : null,
            'quantity' => $this->faker->numberBetween(1, 10),
            'image_url' => $this->faker->boolean(80) ? $this->faker->imageUrl() : null,
            'handle' => strtolower(str_replace(' ', '-', $productTitle)) . '-' . $this->faker->randomNumber(5),
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
