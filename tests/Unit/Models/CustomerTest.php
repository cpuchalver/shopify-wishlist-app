<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function test_has_uuid_as_primary_key()
    {
        $customer = Customer::factory()->create();

        $this->assertIsString($customer->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $customer->id);
    }

    public function test_has_fillable_attributes()
    {
        $fillable = ['shop_id', 'shopify_customer_id', 'email', 'first_name', 'last_name', 'phone', 'accepts_marketing'];

        $this->assertEquals($fillable, Customer::factory()->create()->getFillable());
    }

    public function test_belongs_to_a_shop()
    {
        $shop = Shop::factory()->create();
        $customer = Customer::factory()->forShop($shop)->create();

        $this->assertInstanceOf(Shop::class, $customer->shop);
        $this->assertEquals($shop->id, $customer->shop->id);
    }

    public function test_can_have_wishlists()
    {
        $customer = Customer::factory()->create();
        $wishlists = Wishlist::factory(3)->forCustomer($customer)->create();

        $this->assertInstanceOf(Collection::class, $customer->wishlists);
        $this->assertCount(3, $customer->wishlists);
    }

    public function test_has_correct_default_values()
    {
        $customer = Customer::factory()->create();

        $this->assertFalse($customer->accepts_marketing);
        $this->assertNotNull($customer->first_name);
        $this->assertNotNull($customer->last_name);
        $this->assertNotNull($customer->email);
        $this->assertNotNull($customer->phone);
    }

    public function test_has_correct_casts()
    {
        $customer = Customer::factory()->create(['accepts_marketing' => true]);

        $this->assertIsBool($customer->accepts_marketing);
    }

    public function test_has_unique_shopify_customer_id()
    {
        Customer::factory()->create(['shopify_customer_id' => 'gid://shopify/Customer/123']);

        $this->expectException(QueryException::class);
        Customer::factory()->create(['shopify_customer_id' => 'gid://shopify/Customer/123']);
    }

    public function test_deletes_with_shop_when_shop_is_deleted()
    {
        $shop = Shop::factory()->create();
        $customer = Customer::factory()->forShop($shop)->create();

        $shop->delete();

        $this->assertNull(Customer::find($customer->id));
    }
}
