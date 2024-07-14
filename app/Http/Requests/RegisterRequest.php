<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|regex:/^\+36\d{9}$/|unique:users',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'string' => __('validation.string'),
            'email' => __('validation.email'),
            'unique' => __('validation.unique'),
            'max' => [
                'string' => __('validation.max.string', ['max' => ':max']),
            ],
            'regex' => __('validation.regex'),
            'min' => [
                'string' => __('validation.min.string', ['min' => ':min']),
            ],
        ];
    }
}
