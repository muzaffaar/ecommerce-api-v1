<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariation>
 */
class ProductVariationFactory extends Factory
{
    protected $model = ProductVariation::class;

    public function definition()
    {
        $type = $this->faker->randomElement(['size', 'color']);
        $sizes = ['Small', 'Medium', 'Large', 'X-Large'];
        $colors = ['Red', 'Blue', 'Green', 'Yellow'];

        $value = $type === 'size' ? $this->faker->randomElement($sizes) : $this->faker->randomElement($colors);

        return [
            'type' => $type,
            'value' => $value,
            'price' => $this->faker->randomFloat(2, 10, 200),
        ];
    }
}
