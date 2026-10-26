<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Test Shop',
            'domain' => $this->faker->unique()->domainWord() . '.myshopify.com',
            'shopify_shop_id' => 'gid://shopify/Shop/' . $this->faker->unique()->bothify('########'),
            'access_token' => Str::random(64),
            'email' => $this->faker->safeEmail(),
            'status' => 'active',
            'installed_at' => now(),
        ];
    }

    /**
     * Create an inactive shop.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Create a shop without access token (not yet installed).
     */
    public function notInstalled(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_token' => null,
            'installed_at' => null,
            'status' => 'pending',
        ]);
    }
}
