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

            // Variants
            'variants' => ['sometimes', 'array'],
            'variants.*.id' => ['sometimes', 'exists:variants,id'],
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
            'images.*.id' => ['sometimes', 'exists:images,id'],
            'images.*.shopify_id' => ['sometimes', 'string', 'max:255'],
            'images.*.src' => ['required_with:images', 'string', 'url'],
            'images.*.position' => ['sometimes', 'integer'],
            'images.*.alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'images.*.width' => ['sometimes', 'integer'],
            'images.*.height' => ['sometimes', 'integer'],
            'images.*._delete' => ['sometimes', 'boolean'],

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
