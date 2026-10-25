<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

class WishlistItemTest extends TestCase
{
    public function test_has_uuid_as_primary_key()
    {
        $item = WishlistItem::factory()->create();
        
        $this->assertIsString($item->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $item->id);
    }

    public function test_has_fillable_attributes()
    {
        $fillable = [
            'wishlist_id', 'shopify_product_id', 'shopify_variant_id', 
            'product_title', 'variant_title', 'price', 'compare_at_price', 
            'quantity', 'image_url', 'handle'
        ];
        
        $this->assertEquals($fillable, WishlistItem::factory()->create()->getFillable());
    }

    public function test_belongs_to_a_wishlist()
    {
        $wishlist = Wishlist::factory()->create();
        $item = WishlistItem::factory()->forWishlist($wishlist)->create();
        
        $this->assertInstanceOf(Wishlist::class, $item->wishlist);
        $this->assertEquals($wishlist->id, $item->wishlist->id);
    }

    public function test_has_correct_default_values()
    {
        $item = WishlistItem::factory()->create();
        
        $this->assertEquals(1, $item->quantity);
        $this->assertNull($item->shopify_variant_id);
        $this->assertNull($item->variant_title);
        $this->assertNull($item->compare_at_price);
        $this->assertNull($item->image_url);
        $this->assertNotNull($item->handle);
    }

    public function test_has_correct_casts()
    {
        $item = WishlistItem::factory()->create([
            'price' => 19.99,
            'compare_at_price' => 24.99,
            'quantity' => 3,
        ]);
        
        // Prices are cast as decimal, which may be string or float depending on database
        $this->assertIsNumeric($item->price);
        $this->assertIsNumeric($item->compare_at_price);
        $this->assertIsInt($item->quantity);
    }

    public function test_deletes_with_wishlist_when_wishlist_is_deleted()
    {
        $wishlist = Wishlist::factory()->create();
        $item = WishlistItem::factory()->forWishlist($wishlist)->create();
        
        $wishlist->delete();
        
        $this->assertNull(WishlistItem::find($item->id));
    }

    public function test_can_have_decimal_prices_with_2_decimals()
    {
        $item = WishlistItem::factory()->create([
            'price' => 123.45,
            'compare_at_price' => 150.00,
        ]);
        
        $this->assertEquals(123.45, $item->price);
        $this->assertEquals(150.00, $item->compare_at_price);
    }

    public function test_has_shopify_product_id()
    {
        $item = WishlistItem::factory()->create([
            'shopify_product_id' => 'gid://shopify/Product/123456789',
        ]);
        
        $this->assertEquals('gid://shopify/Product/123456789', $item->shopify_product_id);
    }

    public function test_can_have_variant_information()
    {
        $item = WishlistItem::factory()->create([
            'shopify_variant_id' => 'gid://shopify/ProductVariant/987654321',
            'variant_title' => 'Large - Red',
        ]);
        
        $this->assertEquals('gid://shopify/ProductVariant/987654321', $item->shopify_variant_id);
        $this->assertEquals('Large - Red', $item->variant_title);
    }
}
