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
            'images' => 'nullable|array', // Optional images
            'images.*.id' => 'sometimes|exists:product_images,id',
            'images.*.url' => 'required|string',
            'images.*.is_primary' => 'boolean',
            'tags' => 'nullable|array', // Optional tags
            'tags.*' => 'exists:tags,id',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a string.',
            'name.unique' => 'The product name has already been taken.',
            'description.required' => 'The product description is required.',
            'description.string' => 'The product description must be a string.',
            'price.required' => 'The product price is required.',
            'price.numeric' => 'The product price must be a number.',
            'price.min' => 'The product price must be at least :min.',
            'stock.required' => 'The product stock quantity is required.',
            'stock.integer' => 'The product stock quantity must be an integer.',
            'stock.min' => 'The product stock quantity must be at least :min.',
            'category_id.required' => 'Please select a category for the product.',
            'category_id.exists' => 'The selected category does not exist.',
            'variations.*.type.required' => 'Each variation must have a type.',
            'variations.*.type.string' => 'The variation type must be a string.',
            'variations.*.value.required' => 'Each variation must have a value.',
            'variations.*.value.string' => 'The variation value must be a string.',
            'variations.*.price.required' => 'Each variation must have a price.',
            'variations.*.price.numeric' => 'The variation price must be a number.',
            'variations.*.price.min' => 'The variation price must be at least :min.',
            'images.*.url.required' => 'Each image must have a URL.',
            'images.*.url.string' => 'The image URL must be a string.',
            'images.*.is_primary.boolean' => 'The is_primary field must be true or false.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
        ];
    }
}
