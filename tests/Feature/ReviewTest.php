<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $admin;
    protected $user;
    protected $product;
    protected $order;
    protected $orderItem;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->user = \App\Models\User::factory()->create();

        $this->product = Product::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
        $this->orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
        ]);
    }

    /**
     * Test store method in ReviewController.
     *
     * @return void
     */
    public function testStoreReview()
    {
        // Make a POST request to store a review
        $response = $this->actingAs($this->user)->postJson(route('reviews.store', ['orderItemId' => $this->orderItem->id]), [
            'rating' => 4,
            'review' => 'Great product!',
        ]);


        // Assert the response
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Review added successfully',
                 ]);

        // Check if the review was stored in the database
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Great product!',
        ]);
    }

    /**
     * Test update method in ReviewController.
     *
     * @return void
     */
    public function testUpdateReview()
    {
        // Create a review to update
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // Acting as the authenticated user
        $this->actingAs($this->user);

        // Make a PUT request to update the review
        $response = $this->putJson(route('reviews.update', ['id' => $review->id]), [
            'rating' => 5,
            'review' => 'Updated comment',
        ]);


        // Assert the response
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Review updated successfully',
                 ]);

        // Check if the review was updated in the database
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'review' => 'Updated comment',
        ]);
    }

    /**
     * Test delete method in ReviewController.
     *
     * @return void
     */
    public function testDeleteReview()
    {
        // Create a review to delete
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // Acting as the authenticated user
        $this->actingAs($this->admin);

        // Make a DELETE request to delete the review
        $response = $this->deleteJson(route('reviews.destroy', ['id' => $review->id]));

        // Assert the response
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Review deleted successfully',
                 ]);

        // Check if the review was deleted from the database
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
