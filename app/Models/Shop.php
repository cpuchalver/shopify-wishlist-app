<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'domain',
        'shopify_shop_id',
        'access_token',
        'email',
        'status',
        'installed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'installed_at' => 'datetime',
    ];

    /**
     * Get the customers for this shop.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the wishlists for this shop.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
