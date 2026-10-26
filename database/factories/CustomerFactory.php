<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
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
            'shopify_customer_id' => 'gid://shopify/Customer/' . $this->faker->unique()->bothify('########'),
            'email' => $this->faker->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'accepts_marketing' => false,
        ];
    }

    /**
     * Create a customer with marketing acceptance.
     */
    public function withMarketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_marketing' => true,
        ]);
    }

    /**
     * Create a customer without marketing acceptance.
     */
    public function withoutMarketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_marketing' => false,
        ]);
    }

    /**
     * Create a customer for a specific shop.
     */
    public function forShop(Shop $shop): static
    {
        return $this->state(fn (array $attributes) => [
            'shop_id' => $shop->id,
        ]);
    }
}
