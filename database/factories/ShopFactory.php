<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        $domain = $this->faker->domainName();

        return [
            'name' => $this->faker->company(),
            'domain' => "{$domain}.myshopify.com",
            'shopify_shop_id' => 'gid://shopify/Shop/' . $this->faker->uuid(),
            'access_token' => $this->faker->sha256(),
            'email' => $this->faker->companyEmail(),
            'status' => 'active',
            'installed_at' => $this->faker->dateTimeThisYear(),
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
