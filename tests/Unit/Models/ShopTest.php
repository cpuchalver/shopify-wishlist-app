<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ShopTest extends TestCase
{
    public function test_has_uuid_as_primary_key()
    {
        $shop = Shop::factory()->create();

        $this->assertIsString($shop->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $shop->id);
    }

    public function test_has_fillable_attributes()
    {
        $fillable = ['name', 'domain', 'shopify_shop_id', 'access_token', 'email', 'status', 'installed_at'];

        $this->assertEquals($fillable, Shop::factory()->create()->getFillable());
    }

    public function test_has_correct_default_values()
    {
        $shop = Shop::factory()->create();

        $this->assertEquals('active', $shop->status);
        $this->assertNotNull($shop->access_token);
        $this->assertNotNull($shop->email);
        $this->assertNotNull($shop->installed_at);
    }

    public function test_can_have_customers()
    {
        $shop = Shop::factory()->create();
        $customers = Customer::factory(3)->forShop($shop)->create();

        $this->assertInstanceOf(Collection::class, $shop->customers);
        $this->assertCount(3, $shop->customers);
    }

    public function test_can_have_wishlists()
    {
        $shop = Shop::factory()->create();
        $wishlists = Wishlist::factory(5)->forShop($shop)->create();

        $this->assertInstanceOf(Collection::class, $shop->wishlists);
        $this->assertCount(5, $shop->wishlists);
    }

    public function test_has_correct_casts()
    {
        $shop = Shop::factory()->create(['installed_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $shop->installed_at);
    }

    public function test_has_unique_domain()
    {
        Shop::factory()->create(['domain' => 'test.myshopify.com']);

        $this->expectException(QueryException::class);
        Shop::factory()->create(['domain' => 'test.myshopify.com']);
    }

    public function test_has_unique_shopify_shop_id()
    {
        Shop::factory()->create(['shopify_shop_id' => 'gid://shopify/Shop/123']);

        $this->expectException(QueryException::class);
        Shop::factory()->create(['shopify_shop_id' => 'gid://shopify/Shop/123']);
    }
}
