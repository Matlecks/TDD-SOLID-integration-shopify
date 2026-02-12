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
