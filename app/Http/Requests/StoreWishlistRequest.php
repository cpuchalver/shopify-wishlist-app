<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishlistRequest extends FormRequest
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
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
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
            'customer_id.required' => 'The customer ID is required.',
            'customer_id.uuid' => 'The customer ID must be a valid UUID.',
            'customer_id.exists' => 'The specified customer does not exist.',
            'name.required' => 'The wishlist name is required.',
            'name.string' => 'The wishlist name must be a string.',
            'name.max' => 'The wishlist name may not be greater than 255 characters.',
            'is_default.boolean' => 'The is_default field must be a boolean.',
        ];
    }
}
