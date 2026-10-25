<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
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
        'wishlist_id',
        'shopify_product_id',
        'shopify_variant_id',
        'product_title',
        'variant_title',
        'price',
        'compare_at_price',
        'quantity',
        'image_url',
        'handle',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the wishlist that owns the item.
     */
    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }
}
