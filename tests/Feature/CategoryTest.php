<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'phone_verified_at' => now()
        ]);

        // Create a non-admin user
        $this->nonAdminUser = User::factory()->create([
            'role' => 'user', // Assuming 'user' is a non-admin role
            'phone_verified_at' => now(),
        ]);
    }

    /** @test */
    public function admin_can_store_a_category()
    {
        $category = Category::factory()->create();  // Create a category to use as a parent

        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'parent_id' => $category->id,  // Use the existing category's ID
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.categories.store'), $data);

        // dd($response->json());

        $response->assertStatus(201);

        $this->assertDatabaseHas('categories', $data);

    }

    /** @test */
    public function admin_can_update_a_category()
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Updated Category',
            'parent_id' => null,
        ];

        $response = $this->actingAs($this->adminUser)->putJson(route('admin.categories.update', $category->id), $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Category']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /** @test */
    public function admin_can_destroy_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->adminUser)->deleteJson(route('admin.categories.destroy', $category->id));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /** @test */
    public function it_can_list_categories()
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->getJson(route('categories.index'));

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }
    
    /** @test */
    public function admin_can_list_categories()
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.categories.index'));

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_show_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson(route('categories.show', $category->id));

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $category->name]);
    }
    

    /** @test */
    public function admin_can_show_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.categories.show', $category->id));

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $category->name]);
    }

    /** @test */
    public function non_admin_cannot_store_a_category()
    {
        $data = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'parent_id' => null,
        ];

        $response = $this->actingAs($this->nonAdminUser)->postJson(route('admin.categories.store'), $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_update_a_category()
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'parent_id' => null,
        ];

        $response = $this->actingAs($this->nonAdminUser)->putJson(route('admin.categories.update', $category->id), $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_destroy_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->nonAdminUser)->deleteJson(route('admin.categories.destroy', $category->id));

        $response->assertStatus(403);
    }
}
