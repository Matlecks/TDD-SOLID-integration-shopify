<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->product;

        return [
            'shopify_id' => ['sometimes', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'body_html' => ['sometimes', 'nullable', 'string'],
            'vendor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'product_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'handle' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'handle')->ignore($productId)
            ],
            'status' => ['sometimes', 'string', Rule::in(['active', 'draft', 'archived'])],
            'published_scope' => ['sometimes', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'shopify_updated_at' => ['sometimes', 'nullable', 'date'],

            // Options
            'options' => ['sometimes', 'array'],
            'options.*.id' => ['sometimes', 'exists:options,id'],
            'options.*.shopify_id' => ['sometimes', 'string', 'max:255'],
            'options.*.name' => ['sometimes', 'string', 'max:255'],
            'options.*.position' => ['sometimes', 'integer'],
            'options.*.values' => ['sometimes', 'array'],
            'options.*.values.*' => ['string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status') && is_string($this->status)) {
            $this->merge([
                'status' => strtolower($this->status)
            ]);
        }
    }
}
