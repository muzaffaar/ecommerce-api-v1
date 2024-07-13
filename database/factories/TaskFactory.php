<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'shipping_address_id' => \App\Models\ShippingAddress::factory(),
            'courier_id' => null, // Initially, no courier is assigned
            'status' => 'pending', // Default status
            // Add other necessary fields and their dummy values
        ];
    }
}
