<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWishlistItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'shopify_product_id' => ['required', 'string', 'max:255'],
            'shopify_variant_id' => ['nullable', 'string', 'max:255'],
            'product_title' => ['required', 'string', 'max:255'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['integer', 'min:1', 'max:100'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'handle' => ['nullable', 'string', 'max:255'],
        ];
    }
}
