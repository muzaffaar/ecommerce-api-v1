<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(1, 10), // Replace with actual user IDs if needed
            'product_id' => rand(1, 20), // Replace with actual product IDs if needed
            'rating' => $this->faker->numberBetween(1, 5), // Random rating between 1 to 5
            'review' => $this->faker->paragraph,
            'is_approved' => $this->faker->boolean(), 
        ];
    }
}
