<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'parent_id' => null, // Top-level categories will have parent_id as null
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Category $category) {
            if ($category->parent_id === null) {
                $this->createNestedCategories($category, 3); // Create 4 levels of categories (0 + 3 nested)
            }
        });
    }

    protected function createNestedCategories(Category $parent, $depth)
    {
        if ($depth <= 0) {
            return;
        }

        Category::factory()
            ->count(3) // Create 3 subcategories for each category
            ->create(['parent_id' => $parent->id])
            ->each(function ($child) use ($depth) {
                $this->createNestedCategories($child, $depth - 1);
            });
    }
}
