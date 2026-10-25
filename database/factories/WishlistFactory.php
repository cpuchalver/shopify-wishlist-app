<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'customer_id' => Customer::factory(),
            'name' => Str::random(10) . ' Wishlist',
            'is_default' => false,
        ];
    }

    /**
     * Create a default wishlist.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Create a wishlist for a specific shop.
     */
    public function forShop(Shop $shop): static
    {
        return $this->state(fn (array $attributes) => [
            'shop_id' => $shop->id,
        ]);
    }

    /**
     * Create a wishlist for a specific customer.
     */
    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
            'shop_id' => $customer->shop_id,
        ]);
    }
}
