<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition()
    {
        return [
            'user_id' => null, // This can be nullable depending on your application logic
            'total_quantity' => 0,
            'total_price' => 0.0,
            'is_active' => true,
            'completed_at' => null,
            'discount_code' => null,
            'shipping_method' => null,
            'payment_status' => 'pending',
        ];
    }
}
