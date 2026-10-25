<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;

class WishlistTest extends TestCase
{
    public function test_has_uuid_as_primary_key()
    {
        $wishlist = Wishlist::factory()->create();
        
        $this->assertIsString($wishlist->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $wishlist->id);
    }

    public function test_has_fillable_attributes()
    {
        $fillable = ['shop_id', 'customer_id', 'name', 'is_default'];
        
        $this->assertEquals($fillable, Wishlist::factory()->create()->getFillable());
    }

    public function test_belongs_to_a_shop()
    {
        $shop = Shop::factory()->create();
        $wishlist = Wishlist::factory()->forShop($shop)->create();
        
        $this->assertInstanceOf(Shop::class, $wishlist->shop);
        $this->assertEquals($shop->id, $wishlist->shop->id);
    }

    public function test_belongs_to_a_customer()
    {
        $customer = Customer::factory()->create();
        $wishlist = Wishlist::factory()->forCustomer($customer)->create();
        
        $this->assertInstanceOf(Customer::class, $wishlist->customer);
        $this->assertEquals($customer->id, $wishlist->customer->id);
    }

    public function test_can_have_items()
    {
        $wishlist = Wishlist::factory()->create();
        $items = \App\Models\WishlistItem::factory(5)->forWishlist($wishlist)->create();
        
        $this->assertInstanceOf(Collection::class, $wishlist->items);
        $this->assertCount(5, $wishlist->items);
    }

    public function test_has_correct_default_values()
    {
        $wishlist = Wishlist::factory()->create();
        
        $this->assertFalse($wishlist->is_default);
    }

    public function test_has_correct_casts()
    {
        $wishlist = Wishlist::factory()->create(['is_default' => true]);
        
        $this->assertIsBool($wishlist->is_default);
    }

    public function test_deletes_with_shop_when_shop_is_deleted()
    {
        $shop = Shop::factory()->create();
        $customer = Customer::factory()->forShop($shop)->create();
        $wishlist = Wishlist::factory()->forCustomer($customer)->create();
        
        $shop->delete();
        
        $this->assertNull(Wishlist::find($wishlist->id));
    }

    public function test_deletes_with_customer_when_customer_is_deleted()
    {
        $customer = Customer::factory()->create();
        $wishlist = Wishlist::factory()->forCustomer($customer)->create();
        
        $customer->delete();
        
        $this->assertNull(Wishlist::find($wishlist->id));
    }
}
