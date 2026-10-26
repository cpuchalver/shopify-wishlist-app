<?php

use App\Models\Customer;
use App\Models\Shop;
use App\Models\Wishlist;
use App\Models\WishlistItem;

test('shop endpoints perform crud operations', function () {
    $shop = Shop::factory()->create();

    $response = $this->getJson('/api/shops');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $shop->id);

    $response = $this->postJson('/api/shops', [
        'name' => 'New Shop',
        'domain' => 'new-shop.myshopify.com',
        'shopify_shop_id' => 'gid://shopify/Shop/123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Shop');

    $response = $this->getJson("/api/shops/{$shop->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $shop->id);

    $response = $this->patchJson("/api/shops/{$shop->id}", [
        'name' => 'Renamed Shop',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Renamed Shop');

    $response = $this->deleteJson("/api/shops/{$shop->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
});

test('shop validation rejects missing name', function () {
    $response = $this->postJson('/api/shops', [
        'domain' => 'incomplete.myshopify.com',
        'shopify_shop_id' => 'gid://shopify/Shop/123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('customer endpoints perform crud operations', function () {
    $shop = Shop::factory()->create();
    $customer = Customer::factory()->forShop($shop)->create();

    $response = $this->getJson("/api/customers?shop_id={$shop->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $customer->id);

    $response = $this->postJson('/api/customers', [
        'shop_id' => $shop->id,
        'shopify_customer_id' => 'gid://shopify/Customer/456',
        'email' => 'new@example.com',
        'first_name' => 'New',
        'last_name' => 'Customer',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'new@example.com');

    $response = $this->getJson("/api/customers/{$customer->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $customer->id);

    $response = $this->patchJson("/api/customers/{$customer->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.first_name', 'Updated');

    $response = $this->deleteJson("/api/customers/{$customer->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});

test('customer validation rejects invalid email', function () {
    $shop = Shop::factory()->create();

    $response = $this->postJson('/api/customers', [
        'shop_id' => $shop->id,
        'shopify_customer_id' => 'gid://shopify/Customer/456',
        'email' => 'not-an-email',
        'first_name' => 'New',
        'last_name' => 'Customer',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('wishlist endpoints perform crud operations', function () {
    $shop = Shop::factory()->create();
    $customer = Customer::factory()->forShop($shop)->create();
    $wishlist = Wishlist::factory()->forCustomer($customer)->create();
    WishlistItem::factory()->count(2)->forWishlist($wishlist)->create();

    $response = $this->getJson("/api/wishlists?customer_id={$customer->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $wishlist->id)
        ->assertJsonCount(2, 'data.0.items');

    $response = $this->postJson('/api/wishlists', [
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'name' => 'Birthday Ideas',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Birthday Ideas');

    $response = $this->getJson("/api/wishlists/{$wishlist->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $wishlist->id)
        ->assertJsonPath('data.customer.id', $customer->id);

    $response = $this->patchJson("/api/wishlists/{$wishlist->id}", [
        'name' => 'Renamed Wishlist',
        'is_default' => true,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Renamed Wishlist')
        ->assertJsonPath('data.is_default', true);

    $response = $this->deleteJson("/api/wishlists/{$wishlist->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
});

test('wishlist validation rejects unknown customer', function () {
    $shop = Shop::factory()->create();

    $response = $this->postJson('/api/wishlists', [
        'shop_id' => $shop->id,
        'customer_id' => '01943064-0000-7000-8000-000000000000',
        'name' => 'Orphan Wishlist',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('customer_id');
});

test('wishlist item endpoints perform crud operations', function () {
    $wishlist = Wishlist::factory()->create();
    $item = WishlistItem::factory()->forWishlist($wishlist)->create();

    $response = $this->getJson("/api/wishlists/{$wishlist->id}/items");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item->id);

    $response = $this->postJson("/api/wishlists/{$wishlist->id}/items", [
        'shopify_product_id' => 'gid://shopify/Product/789',
        'product_title' => 'New Product',
        'price' => 29.99,
        'quantity' => 2,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.product_title', 'New Product')
        ->assertJsonPath('data.quantity', 2);

    $response = $this->getJson("/api/items/{$item->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $item->id);

    $response = $this->patchJson("/api/items/{$item->id}", [
        'quantity' => 5,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.quantity', 5);

    $response = $this->deleteJson("/api/items/{$item->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('wishlist_items', ['id' => $item->id]);
});

test('wishlist item validation rejects missing product title', function () {
    $wishlist = Wishlist::factory()->create();

    $response = $this->postJson("/api/wishlists/{$wishlist->id}/items", [
        'shopify_product_id' => 'gid://shopify/Product/789',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('product_title');
});

test('item from another wishlist is not accessible', function () {
    $wishlist = Wishlist::factory()->create();
    $otherWishlist = Wishlist::factory()->create();
    $item = WishlistItem::factory()->forWishlist($otherWishlist)->create();

    $response = $this->getJson("/api/wishlists/{$wishlist->id}/items/{$item->id}");

    $response->assertNotFound();
});
