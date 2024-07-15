<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_can_list_all_tags()
    {
        Tag::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson(route('admin.tags.index'));

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_a_tag()
    {
        $tagData = ['name' => 'New Tag'];

        $response = $this->actingAs($this->admin)->postJson(route('admin.tags.store'), $tagData);

        $response->assertStatus(201)
                 ->assertJsonFragment($tagData);

        $this->assertDatabaseHas('tags', $tagData);
    }

    /** @test */
    public function it_can_show_a_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin)->getJson(route('admin.tags.show', $tag));

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $tag->name]);
    }

    /** @test */
    public function it_can_update_a_tag()
    {
        $tag = Tag::factory()->create();
        $updateData = ['name' => 'Updated Tag'];

        $response = $this->actingAs($this->admin)->putJson(route('admin.tags.update', $tag), $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('tags', $updateData);
    }

    /** @test */
    public function it_can_delete_a_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.tags.destroy', $tag));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    /** @test */
    public function it_can_attach_tag_to_product()
    {
        $tag = Tag::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->postJson(route('admin.tags.attachTagToProduct', $product), [
            'tag_id' => $tag->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Tag attached to product successfully']);
        $this->assertTrue($product->tags()->where('tags.id', $tag->id)->exists());
    }

    /** @test */
    public function it_can_detach_tag_from_product()
    {
        $tag = Tag::factory()->create();
        $product = Product::factory()->create();
        $product->tags()->attach($tag->id);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.tags.detachTagFromProduct', $product), ['tag_id' => $tag->id]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Tag detached from product successfully']);

        $this->assertDatabaseMissing('product_tag', ['product_id' => $product->id, 'tag_id' => $tag->id]);
    }
}
