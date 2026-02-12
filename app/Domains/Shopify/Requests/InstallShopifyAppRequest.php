<?php

namespace App\Domains\Shopify\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallShopifyAppRequest extends FormRequest
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
            'shop' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/',
            ],
        ];
    }

    /**
     * Returns custom validation error messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shop.required' => 'The "shop" parameter is required.',
            'shop.string' => 'The "shop" parameter must be a string.',
            'shop.max' => 'The "shop" parameter cannot exceed :max characters.',
            'shop.regex' => 'The store domain must be a valid Shopify domain (example.myshopify.com).',
        ];
    }
}
