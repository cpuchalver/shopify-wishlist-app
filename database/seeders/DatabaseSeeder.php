<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create shops
        $shops = Shop::factory(5)->create();

        // Create customers for each shop
        foreach ($shops as $shop) {
            $customers = Customer::factory(rand(10, 50))->forShop($shop)->create();

            // Create wishlists for each customer
            foreach ($customers as $customer) {
                $wishlists = Wishlist::factory(rand(1, 3))->forCustomer($customer)->create();

                // Create items for each wishlist
                foreach ($wishlists as $wishlist) {
                    WishlistItem::factory(rand(1, 10))->forWishlist($wishlist)->create();
                }
            }
        }
    }
}
