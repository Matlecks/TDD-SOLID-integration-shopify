<?php

namespace App\Domains\Shopify\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Domains\Shopify\Exceptions\ShopifyAuthException;

class HandleShopifyCallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'shop' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/',
            ],
            'code' => [
                'required',
                'string',
                'min:1',
            ],
            'hmac' => [
                'sometimes',
                'string',
            ],
            'timestamp' => [
                'sometimes',
                'integer',
            ],
            'state' => [
                'sometimes',
                'string',
            ],
            'host' => [
                'sometimes',
                'string',
            ],
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
            'shop.required' => 'Shop parameter is required.',
            'shop.regex' => 'Invalid shop domain format. Must be a valid myshopify.com domain.',
            'code.required' => 'Authorization code is required.',
            'code.min' => 'Authorization code cannot be empty.',
        ];
    }
}
