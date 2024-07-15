<?php

// database/factories/ProductFactory.php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Variation;
use App\Models\Image;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $name = $this->faker->unique()->word;
        $category = Category::factory()->create();

        return [
            'name' => $name,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'stock' => $this->faker->numberBetween(1, 100),
            'category_id' => $category->id,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            // Create variations
            ProductVariation::factory()->count(2)->create(['product_id' => $product->id]);

            // Create images
            ProductImage::factory()->count(2)->create(['product_id' => $product->id]);

            // Attach tags
            $tags = Tag::inRandomOrder()->take(3)->pluck('id');
            $product->tags()->attach($tags);
            
            // Create reviews
            for($i=0; $i < 15; $i++)
            {
                Review::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => User::factory(), // Replace with actual user IDs if needed
                    'rating' => $this->faker->numberBetween(1, 5), // Adjust as per your rating system
                    'review' => $this->faker->paragraph,
                ]);
            }
        });
    }
}
