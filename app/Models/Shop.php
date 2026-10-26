<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory, HasUuids;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

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
        'refresh_token',
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
