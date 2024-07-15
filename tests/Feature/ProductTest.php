<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $adminUser;
    protected $nonAdminUser;
    protected $product;
    protected $tags;
    protected $variations;
    protected $images;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'phone_verified_at' => now(),
        ]);

        $this->nonAdminUser = User::factory()->create([
            'role' => 'user',
            'phone_verified_at' => now(),
        ]);

        $this->product = Product::factory()->create();
        $this->tags = Tag::factory()->count(3)->create();
        $this->variations = ProductVariation::factory()->count(2)->create(['product_id' => $this->product->id]);
        $this->images = ProductImage::factory()->count(2)->create(['product_id' => $this->product->id]);
        $this->product->tags()->attach($this->tags);
    }

    /** @test */
    public function it_can_list_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $variations = ProductVariation::factory()->count(3)->create(['product_id' => $product->id]);
        $images = ProductImage::factory()->count(2)->create(['product_id' => $product->id, 'is_primary' => true]);

        $response = $this->getJson(route('products.index'));

        $response->assertStatus(200);

        $jsonData = $response->json();

        $responseProduct = collect($jsonData)->firstWhere('id', $product->id);

        $this->assertNotNull($responseProduct, "Product not found in response");

        foreach ($images as $image) {
            $imageFound = collect($responseProduct['images'])->firstWhere('id', $image->id);
            $this->assertNotNull($imageFound, "Image with id {$image->id} not found in response product");
            $this->assertEquals($image->product_id, $imageFound['product_id'], "Product ID mismatch for image {$image->id}");
            $this->assertEquals($image->url, $imageFound['url'], "URL mismatch for image {$image->id}");
            $this->assertEquals($image->is_primary, $imageFound['is_primary'], "Primary status mismatch for image {$image->id}");
        }

        foreach ($variations as $variation) {
            $variationFound = collect($responseProduct['variations'])->firstWhere('id', $variation->id);
            $this->assertNotNull($variationFound, "Variation with id {$variation->id} not found in response product");
            $this->assertEquals($variation->product_id, $variationFound['product_id'], "Product ID mismatch for variation {$variation->id}");
            $this->assertEquals($variation->type, $variationFound['type'], "Type mismatch for variation {$variation->id}");
            $this->assertEquals($variation->value, $variationFound['value'], "Value mismatch for variation {$variation->id}");
            $this->assertEquals(number_format($variation->price, 2), $variationFound['price'], "Price mismatch for variation {$variation->id}");
        }

        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'price',
                'stock',
                'category_id',
                'slug',
                'created_at',
                'updated_at',
                'images' => [
                    '*' => [
                        'id',
                        'product_id',
                        'url',
                        'is_primary',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'variations' => [
                    '*' => [
                        'id',
                        'product_id',
                        'type',
                        'value',
                        'price',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_list_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $variations = ProductVariation::factory()->count(3)->create(['product_id' => $product->id]);
        $images = ProductImage::factory()->count(2)->create(['product_id' => $product->id, 'is_primary' => true]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.products.index'));

        $response->assertStatus(200);

        $jsonData = $response->json();

        $responseProduct = collect($jsonData)->firstWhere('id', $product->id);

        $this->assertNotNull($responseProduct, "Product not found in response");

        foreach ($images as $image) {
            $imageFound = collect($responseProduct['images'])->firstWhere('id', $image->id);
            $this->assertNotNull($imageFound, "Image with id {$image->id} not found in response product");
            $this->assertEquals($image->product_id, $imageFound['product_id'], "Product ID mismatch for image {$image->id}");
            $this->assertEquals($image->url, $imageFound['url'], "URL mismatch for image {$image->id}");
            $this->assertEquals($image->is_primary, $imageFound['is_primary'], "Primary status mismatch for image {$image->id}");
        }

        foreach ($variations as $variation) {
            $variationFound = collect($responseProduct['variations'])->firstWhere('id', $variation->id);
            $this->assertNotNull($variationFound, "Variation with id {$variation->id} not found in response product");
            $this->assertEquals($variation->product_id, $variationFound['product_id'], "Product ID mismatch for variation {$variation->id}");
            $this->assertEquals($variation->type, $variationFound['type'], "Type mismatch for variation {$variation->id}");
            $this->assertEquals($variation->value, $variationFound['value'], "Value mismatch for variation {$variation->id}");
            $this->assertEquals(number_format($variation->price, 2), $variationFound['price'], "Price mismatch for variation {$variation->id}");
        }

        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'price',
                'stock',
                'category_id',
                'slug',
                'created_at',
                'updated_at',
                'images' => [
                    '*' => [
                        'id',
                        'product_id',
                        'url',
                        'is_primary',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'variations' => [
                    '*' => [
                        'id',
                        'product_id',
                        'type',
                        'value',
                        'price',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_store_a_product()
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'stock' => $this->faker->numberBetween(1, 100),
            'category_id' => $category->id,
            'variations' => [
                ['type' => 'size', 'value' => 'M', 'price' => 20.00],
            ],
            'images' => [
                ['url' => 'http://example.com/image1.jpg', 'is_primary' => true],
            ],
            'tags' => $tags->pluck('id')->toArray()
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.products.store'), $data);

        $response->assertStatus(201)
                ->assertJsonFragment(['name' => $data['name']]);

        $this->assertDatabaseHas('products', ['name' => $data['name']]);

        $product = Product::where('name', $data['name'])->firstOrFail();

        foreach ($data['variations'] as $variation) {
            $this->assertDatabaseHas('product_variations', [
                'product_id' => $product->id,
                'type' => $variation['type'],
                'value' => $variation['value'],
                'price' => $variation['price'],
            ]);
        }

        foreach ($data['images'] as $image) {
            $this->assertDatabaseHas('product_images', [
                'product_id' => $product->id,
                'url' => $image['url'],
                'is_primary' => $image['is_primary'],
            ]);
        }

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('product_tag', [
                'product_id' => $product->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    /** @test */
    public function it_can_show_a_product()
    {
        $response = $this->getJson(route('products.show', ['slug' => $this->product->slug]));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'price',
            'stock',
            'category_id',
            'slug',
            'category',
            'tags',
            'images',
            'variations'
        ]);

    }

    /** @test */
    public function product_not_found()
    {
        $response = $this->getJson(route('products.show', ['slug' => 'non-existent-slug']));

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Product not found.'
        ]);
    }

    /** @test */
    public function admin_can_show_a_product()
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('products.show', ['slug' => $this->product->slug]));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'price',
            'stock',
            'category_id',
            'slug',
            'category',
            'tags',
            'images',
            'variations'
        ]);
    }

    /** @test */
    public function admin_can_update_a_product()
    {
        $this->actingAs($this->adminUser);

        $newData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => 199.99,
            'stock' => 50,
            'category_id' => $this->product->category_id,
            'variations' => [
                [
                    'id' => $this->variations->first()->id,
                    'type' => 'color',
                    'value' => 'blue',
                    'price' => 20.00,
                ],
                [
                    'type' => 'size',
                    'value' => 'L',
                    'price' => 30.00,
                ],
            ],
            'images' => [
                [
                    'id' => $this->images->first()->id,
                    'url' => 'http://example.com/updated-image.jpg',
                    'is_primary' => true,
                ],
                [
                    'url' => 'http://example.com/new-image.jpg',
                    'is_primary' => false,
                ],
            ],
            'tags' => $this->tags->pluck('id')->toArray(),
        ];

        $response = $this->putJson(route('admin.products.update', ['slug' => $this->product->slug]), $newData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Product Name'])
            ->assertJsonFragment(['description' => 'Updated description'])
            ->assertJsonFragment(['price' => 199.99]);

        $this->assertDatabaseHas('products', ['name' => 'Updated Product Name']);
        $this->assertDatabaseHas('product_variations', ['type' => 'color', 'value' => 'blue']);
        $this->assertDatabaseHas('product_variations', ['type' => 'size', 'value' => 'L']);
        $this->assertDatabaseHas('product_images', ['url' => 'http://example.com/updated-image.jpg', 'is_primary' => true]);
        $this->assertDatabaseHas('product_images', ['url' => 'http://example.com/new-image.jpg', 'is_primary' => false]);
    }

    /** @test */
    public function admin_can_destroy_a_product()
    {
        // Create a product with associated data
        $product = Product::factory()
        ->has(ProductVariation::factory()->count(2), 'variations')
        ->has(ProductImage::factory()->count(2), 'images')
        ->create();

        // Attach tags to the product
        $tags = Tag::factory()->count(3)->create();
        $product->tags()->attach($tags);

        // Perform the delete request as an admin user
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->deleteJson(route('admin.products.destroy', $product->id));

        // Check the response status and message
        $response->assertStatus(200)->assertJson(['message' => 'Product and associated data deleted successfully']);

        // Assert the product and its associations are deleted from the database
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variations', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('product_images', ['product_id' => $product->id]);

        foreach ($tags as $tag) {
            $this->assertDatabaseMissing('product_tag', ['product_id' => $product->id, 'tag_id' => $tag->id]);
        }
    }

    /** @test */
    public function admin_can_update_a_product_with_variations_and_images()
    {
        $this->actingAs($this->adminUser);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 67.72]);
        $variations = ProductVariation::factory()->count(3)->create(['product_id' => $product->id]);
        $images = ProductImage::factory()->count(2)->create(['product_id' => $product->id]);

        $newData = [
            'category_id' => $category->id,
            'name' => 'Updated Product Name',
            'description' => 'Updated product description.',
            'price' => 99.99,
            'stock' => 50,
            'variations' => [
                [
                    'id' => $variations[0]->id,
                    'type' => 'size',
                    'value' => 'M',
                    'price' => 49.99,
                ],
                [
                    'id' => $variations[1]->id,
                    'type' => 'size',
                    'value' => 'L',
                    'price' => 59.99,
                ],
            ],
            'images' => [
                [
                    'id' => $images[0]->id,
                    'url' => 'https://example.com/new-image-url-1.png',
                    'is_primary' => true,
                ],
                [
                    'id' => $images[1]->id,
                    'url' => 'https://example.com/new-image-url-2.png',
                    'is_primary' => false,
                ],
            ],
        ];

        $response = $this->put(route('admin.products.update', ['slug' => $product->slug]), $newData);

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'description' => 'Updated product description.',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $updatedProduct = Product::find($product->id);
        $this->assertEquals('Updated Product Name', $updatedProduct->name);
        $this->assertEquals(99.99, $updatedProduct->price);

        foreach ($newData['variations'] as $updatedVariation) {
            $this->assertDatabaseHas('product_variations', [
                'id' => $updatedVariation['id'],
                'type' => $updatedVariation['type'],
                'value' => $updatedVariation['value'],
                'price' => $updatedVariation['price'],
            ]);
        }

        foreach ($newData['images'] as $updatedImage) {
            $this->assertDatabaseHas('product_images', [
                'id' => $updatedImage['id'],
                'url' => $updatedImage['url'],
                'is_primary' => $updatedImage['is_primary'],
            ]);
        }
    }


    /** @test */
    public function non_admin_cannot_store_a_product()
    {
        $category = Category::factory()->create();
        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'stock' => $this->faker->numberBetween(1, 100),
            'category_id' => $category->id,
        ];
        $response = $this->actingAs($this->nonAdminUser)->postJson(route('admin.products.store'), $data);
        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_update_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id'=>$category->id]);
        $data = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 50.00,
            'stock' => 10,
            'category_id' => $category->id,
        ];
        
        $response = $this->actingAs($this->nonAdminUser)->putJson(route('admin.products.update', $product->slug), $data);
        
        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_destroy_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id'=>$category->id]);

        $response = $this->actingAs($this->nonAdminUser)->deleteJson(route('admin.products.destroy', $product->slug));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_search_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Searchable Product',
            'category_id' => $category->id
        ]);

        $response = $this->getJson(route('products.search', ['query' => 'Searchable']));

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Searchable Product']);
    }
}
