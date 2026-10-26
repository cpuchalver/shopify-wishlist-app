<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWishlistItemRequest extends FormRequest
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
            'product_title' => ['sometimes', 'required', 'string', 'max:255'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'handle' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
