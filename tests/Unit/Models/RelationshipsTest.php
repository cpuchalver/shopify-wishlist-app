<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    public function test_can_create_complete_shop_with_customers_wishlists_and_items()
    {
        $shop = Shop::factory()->create();
        $customer = Customer::factory()->forShop($shop)->create();
        $wishlist = Wishlist::factory()->forCustomer($customer)->create();
        $item = WishlistItem::factory()->forWishlist($wishlist)->create();

        // Verify shop has customer
        $this->assertTrue($shop->customers->contains($customer));

        // Verify shop has wishlist
        $this->assertTrue($shop->wishlists->contains($wishlist));

        // Verify customer has wishlist
        $this->assertTrue($customer->wishlists->contains($wishlist));

        // Verify wishlist has item
        $this->assertTrue($wishlist->items->contains($item));

        // Verify item belongs to wishlist
        $this->assertEquals($wishlist->id, $item->wishlist->id);

        // Verify wishlist belongs to customer
        $this->assertEquals($customer->id, $wishlist->customer->id);

        // Verify wishlist belongs to shop
        $this->assertEquals($shop->id, $wishlist->shop->id);

        // Verify customer belongs to shop
        $this->assertEquals($shop->id, $customer->shop->id);
    }

    public function test_can_navigate_from_shop_to_all_wishlist_items()
    {
        $shop = Shop::factory()->create();

        // Create customers with wishlists and items
        $customers = Customer::factory(3)->forShop($shop)->create();

        foreach ($customers as $customer) {
            $wishlists = Wishlist::factory(2)->forCustomer($customer)->create();

            foreach ($wishlists as $wishlist) {
                WishlistItem::factory(3)->forWishlist($wishlist)->create();
            }
        }

        // Count all items through relationships
        $totalItems = 0;
        foreach ($shop->customers as $customer) {
            foreach ($customer->wishlists as $wishlist) {
                $totalItems += $wishlist->items->count();
            }
        }

        $this->assertEquals(18, $totalItems); // 3 customers * 2 wishlists * 3 items
    }

    public function test_can_find_all_wishlists_for_a_shop_through_customers()
    {
        $shop = Shop::factory()->create();
        $customers = Customer::factory(2)->forShop($shop)->create();

        foreach ($customers as $customer) {
            Wishlist::factory(3)->forCustomer($customer)->create();
        }

        $wishlistsThroughCustomers = $shop->customers->flatMap(fn ($c) => $c->wishlists);
        $wishlistsDirect = $shop->wishlists;

        $this->assertCount($wishlistsDirect->count(), $wishlistsThroughCustomers);
        $this->assertCount(6, $wishlistsDirect); // 2 customers * 3 wishlists
    }

    public function test_can_find_all_items_for_a_customer_through_wishlists()
    {
        $customer = Customer::factory()->create();
        $wishlists = Wishlist::factory(2)->forCustomer($customer)->create();

        foreach ($wishlists as $wishlist) {
            WishlistItem::factory(4)->forWishlist($wishlist)->create();
        }

        $itemsThroughWishlists = $customer->wishlists->flatMap(fn ($w) => $w->items);

        $this->assertCount(8, $itemsThroughWishlists); // 2 wishlists * 4 items
    }

    public function test_cascades_delete_from_shop_to_all_related_models()
    {
        $shop = Shop::factory()->create();
        $customer = Customer::factory()->forShop($shop)->create();
        $wishlist = Wishlist::factory()->forCustomer($customer)->create();
        $item = WishlistItem::factory()->forWishlist($wishlist)->create();

        $shopId = $shop->id;
        $customerId = $customer->id;
        $wishlistId = $wishlist->id;
        $itemId = $item->id;

        $shop->delete();

        // All should be deleted
        $this->assertNull(Shop::find($shopId));
        $this->assertNull(Customer::find($customerId));
        $this->assertNull(Wishlist::find($wishlistId));
        $this->assertNull(WishlistItem::find($itemId));
    }

    public function test_can_query_wishlists_with_their_customer_and_shop()
    {
        $shop = Shop::factory()->create(['name' => 'Test Shop']);
        $customer = Customer::factory()->forShop($shop)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $wishlist = Wishlist::factory()->forCustomer($customer)->create(['name' => 'Favorites']);

        $foundWishlist = Wishlist::with(['customer', 'shop'])->find($wishlist->id);

        $this->assertEquals('Favorites', $foundWishlist->name);
        $this->assertEquals('John', $foundWishlist->customer->first_name);
        $this->assertEquals('Doe', $foundWishlist->customer->last_name);
        $this->assertEquals('Test Shop', $foundWishlist->shop->name);
    }

    public function test_can_query_wishlist_items_with_their_wishlist_customer_and_shop()
    {
        $shop = Shop::factory()->create(['name' => 'Test Shop']);
        $customer = Customer::factory()->forShop($shop)->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
        $wishlist = Wishlist::factory()->forCustomer($customer)->create(['name' => 'Birthday List']);
        $item = WishlistItem::factory()->forWishlist($wishlist)->create([
            'product_title' => 'Test Product',
            'price' => 29.99,
        ]);

        $foundItem = WishlistItem::with(['wishlist.customer', 'wishlist.shop'])->find($item->id);

        $this->assertEquals('Test Product', $foundItem->product_title);
        $this->assertEquals(29.99, $foundItem->price);
        $this->assertEquals('Birthday List', $foundItem->wishlist->name);
        $this->assertEquals('Jane', $foundItem->wishlist->customer->first_name);
        $this->assertEquals('Test Shop', $foundItem->wishlist->shop->name);
    }
}
