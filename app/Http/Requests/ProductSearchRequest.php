<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'tags' => 'sometimes|array',
            'tags.*' => 'string',
            'price_min' => 'sometimes|numeric|min:0',
            'price_max' => 'sometimes|numeric|min:0',
            'rating_min' => 'sometimes|numeric|min:0|max:5',
            'brand' => 'sometimes|string',
            'size' => 'sometimes|string',
            'color' => 'sometimes|string',
            'availability' => 'sometimes|string|in:in stock,out of stock',
            'sort_by' => 'sometimes|string|in:name,price,created_at',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'category_id.exists' => 'The selected category does not exist.',
            'tags.*.string' => 'Each tag must be a string.',
            'price_min.numeric' => 'The minimum price must be a number.',
            'price_min.min' => 'The minimum price must be at least 0.',
            'price_max.numeric' => 'The maximum price must be a number.',
            'price_max.min' => 'The maximum price must be at least 0.',
            'rating_min.numeric' => 'The minimum rating must be a number.',
            'rating_min.min' => 'The minimum rating must be at least 0.',
            'rating_min.max' => 'The minimum rating must not be greater than 5.',
            'availability.in' => 'The availability status must be either "in stock" or "out of stock".',
            'sort_by.in' => 'The sort by field must be one of: name, price, created_at.',
            'sort_order.in' => 'The sort order must be either "asc" or "desc".',
        ];
    }

    
}
