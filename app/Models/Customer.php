<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_id',
        'shopify_customer_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'accepts_marketing',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'accepts_marketing' => 'boolean',
    ];

    /**
     * Get the shop that owns the customer.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the wishlists for this customer.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
