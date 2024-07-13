<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => Str::uuid(),
            'total_amount' => $this->faker->randomFloat(2, 50, 500),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            // Add other attributes as needed
            'user_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
            'billing_address_id' => null, // Adjust based on your application logic
            'shipping_address_id' => null, // Adjust based on your application logic
        ];
    }
}
