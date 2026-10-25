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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('shopify_shop_id')->unique();
            $table->text('access_token')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();

            $table->index('shopify_shop_id');
            $table->index('domain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
