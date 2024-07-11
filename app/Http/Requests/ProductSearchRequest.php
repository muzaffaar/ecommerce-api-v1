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
            'name.string' => 'Name must be a string.',
            'description.string' => 'Description must be a string.',
            'category_id.exists' => 'Category ID does not exist.',
            'tags.array' => 'Tags must be provided as an array.',
            'tags.*.string' => 'Each tag must be a string.',
            'price_min.numeric' => 'Minimum price must be a number.',
            'price_min.min' => 'Minimum price cannot be less than :min.',
            'price_max.numeric' => 'Maximum price must be a number.',
            'price_max.min' => 'Maximum price cannot be less than :min.',
            'rating_min.numeric' => 'Minimum rating must be a number.',
            'rating_min.min' => 'Minimum rating cannot be less than :min.',
            'rating_min.max' => 'Maximum rating cannot be greater than :max.',
            'brand.string' => 'Brand must be a string.',
            'size.string' => 'Size must be a string.',
            'color.string' => 'Color must be a string.',
            'availability.string' => 'Availability must be a string.',
            'availability.in' => 'Availability must be either "in stock" or "out of stock".',
            'sort_by.string' => 'Sort by must be a string.',
            'sort_by.in' => 'Sort by must be one of: :values.',
            'sort_order.string' => 'Sort order must be a string.',
            'sort_order.in' => 'Sort order must be one of: :values.',
        ];
    }

    
}
