<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create root categories with their children
        Category::factory()
            ->count(2) // Number of root categories
            ->create();

            $tags = Tag::factory()->count(10)->create(); // Create 10 tags

            Category::factory()
                ->count(20)
                ->create()
                ->each(function ($category) use ($tags) {
                    $products = Product::factory()->count(5)->create(['category_id' => $category->id]);
    
                    $products->each(function ($product) use ($tags) {
                        $product->tags()->attach($tags->random(3)->pluck('id'));
                        ProductImage::factory()->count(3)->create(['product_id' => $product->id]);
                        ProductVariation::factory()->count(2)->create(['product_id' => $product->id]);
                    });
                });
    }
}
