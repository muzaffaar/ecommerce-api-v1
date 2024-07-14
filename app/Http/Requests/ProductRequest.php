<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        // $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'name' => 'required|string|unique:products,name',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'variations' => 'nullable|array', // Optional variations
            'variations.*.id' => 'sometimes|exists:product_variations,id',
            'variations.*.type' => 'required|string',
            'variations.*.value' => 'required|string',
            'variations.*.price' => 'required|numeric|min:0',
            'images' => 'required|array',
            'images.*.id' => 'sometimes|exists:product_images,id',
            'images.*.url' => 'required|string',
            'images.*.is_primary' => 'boolean',
            'tags' => 'nullable|array', // Optional tags
            'tags.*' => 'exists:tags,id',
        ];
    }
}
