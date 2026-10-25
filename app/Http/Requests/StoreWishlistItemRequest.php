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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shopify_product_id' => ['required', 'string', 'max:255'],
            'shopify_variant_id' => ['nullable', 'string', 'max:255'],
            'product_title' => ['required', 'string', 'max:255'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'handle' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the default values for the request.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'quantity' => 1,
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shopify_product_id.required' => 'The Shopify product ID is required.',
            'shopify_product_id.string' => 'The Shopify product ID must be a string.',
            'shopify_product_id.max' => 'The Shopify product ID may not be greater than 255 characters.',
            'product_title.required' => 'The product title is required.',
            'product_title.string' => 'The product title must be a string.',
            'product_title.max' => 'The product title may not be greater than 255 characters.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'price.max' => 'The price may not be greater than 999999.99.',
            'compare_at_price.numeric' => 'The compare at price must be a number.',
            'compare_at_price.min' => 'The compare at price must be at least 0.',
            'compare_at_price.max' => 'The compare at price may not be greater than 999999.99.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 1.',
            'quantity.max' => 'The quantity may not be greater than 100.',
            'image_url.url' => 'The image URL must be a valid URL.',
            'image_url.max' => 'The image URL may not be greater than 2048 characters.',
        ];
    }
}
