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
        $type = $this->faker->randomElement(['color', 'size']);
        $value = $type === 'color' ? $this->faker->safeColorName() : $this->faker->randomElement(['S', 'M', 'L', 'XL']);

        return [
            'type' => $type,
            'value' => $value,
            'price' => $this->faker->randomFloat(2, 1, 100),
            'product_id' => Product::factory(),
        ];
    }
}
