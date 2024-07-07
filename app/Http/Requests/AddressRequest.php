<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust authorization logic if needed
    }

    public function rules()
    {
        return [
            'billing_address.first_name' => 'required|string',
            'billing_address.last_name' => 'required|string',
            'billing_address.address' => 'required|string',
            'billing_address.city' => 'required|string',
            'billing_address.state' => 'required|string',
            'billing_address.postal_code' => 'required|string',
            'billing_address.country' => 'required|string',
            'billing_address.phone' => 'required|string',

            'shipping_address.first_name' => 'required|string',
            'shipping_address.last_name' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'shipping_address.phone' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'billing_address.first_name.required' => 'The billing first name is required.',
            'billing_address.last_name.required' => 'The billing last name is required.',
            'billing_address.address.required' => 'The billing address is required.',
            'billing_address.city.required' => 'The billing city is required.',
            'billing_address.state.required' => 'The billing state is required.',
            'billing_address.postal_code.required' => 'The billing postal code is required.',
            'billing_address.country.required' => 'The billing country is required.',
            'billing_address.phone.required' => 'The billing phone number is required.',

            'shipping_address.first_name.required' => 'The shipping first name is required.',
            'shipping_address.last_name.required' => 'The shipping last name is required.',
            'shipping_address.address.required' => 'The shipping address is required.',
            'shipping_address.city.required' => 'The shipping city is required.',
            'shipping_address.state.required' => 'The shipping state is required.',
            'shipping_address.postal_code.required' => 'The shipping postal code is required.',
            'shipping_address.country.required' => 'The shipping country is required.',
            'shipping_address.phone.required' => 'The shipping phone number is required.',
        ];
    }
}
