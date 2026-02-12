<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shopify_id' => ['sometimes', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'body_html' => ['sometimes', 'nullable', 'string'],
            'vendor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'product_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'handle' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:products,handle'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'draft', 'archived'])],
            'published_scope' => ['sometimes', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'shopify_created_at' => ['sometimes', 'nullable', 'date'],
            'shopify_updated_at' => ['sometimes', 'nullable', 'date'],

            // Variants
            'variants' => ['sometimes', 'array'],
            'variants.*.shopify_id' => ['sometimes', 'string', 'max:255'],
            'variants.*.title' => ['sometimes', 'string', 'max:255'],
            'variants.*.price' => ['sometimes', 'numeric', 'min:0'],
            'variants.*.sku' => ['sometimes', 'nullable', 'string', 'max:255'],
            'variants.*.position' => ['sometimes', 'integer'],
            'variants.*.inventory_policy' => ['sometimes', 'string', 'max:255'],
            'variants.*.fulfillment_service' => ['sometimes', 'string', 'max:255'],
            'variants.*.inventory_management' => ['sometimes', 'nullable', 'string', 'max:255'],
            'variants.*.option1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'variants.*.option2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'variants.*.option3' => ['sometimes', 'nullable', 'string', 'max:255'],
            'variants.*.taxable' => ['sometimes', 'boolean'],
            'variants.*.weight' => ['sometimes', 'numeric', 'min:0'],
            'variants.*.weight_unit' => ['sometimes', 'string', 'max:10'],
            'variants.*.inventory_quantity' => ['sometimes', 'integer', 'min:0'],

            // Images
            'images' => ['sometimes', 'array'],
            'images.*.shopify_id' => ['sometimes', 'string', 'max:255'],
            'images.*.src' => ['required_with:images', 'string', 'url'],
            'images.*.position' => ['sometimes', 'integer'],
            'images.*.alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'images.*.width' => ['sometimes', 'integer'],
            'images.*.height' => ['sometimes', 'integer'],

            // Options
            'options' => ['sometimes', 'array'],
            'options.*.shopify_id' => ['sometimes', 'string', 'max:255'],
            'options.*.name' => ['sometimes', 'string', 'max:255'],
            'options.*.position' => ['sometimes', 'integer'],
            'options.*.values' => ['sometimes', 'array'],
            'options.*.values.*' => ['string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название товара обязательно для заполнения',
            'handle.unique' => 'Этот URL-адрес товара уже используется',
            'variants.*.price.min' => 'Цена товара не может быть отрицательной',
            'images.*.src.url' => 'Неверный формат URL изображения',
            'images.*.src.required_with' => 'URL изображения обязателен',
        ];
    }
}
