<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wishlist_id');
            $table->string('shopify_product_id');
            $table->string('shopify_variant_id')->nullable();
            $table->string('product_title');
            $table->string('variant_title')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('image_url')->nullable();
            $table->string('handle')->nullable();
            $table->timestamps();

            $table->foreign('wishlist_id')->references('id')->on('wishlists')->cascadeOnDelete();

            $table->index('wishlist_id');
            $table->index('shopify_product_id');
            $table->index('shopify_variant_id');
            $table->index(['wishlist_id', 'shopify_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
