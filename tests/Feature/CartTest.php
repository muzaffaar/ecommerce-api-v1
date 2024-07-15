<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\CartController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and product to use in the tests
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_add_item_to_cart()
    {
        $response = $this->postJson(route('carts.add-item'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'variations' => [
                [
                    'type' => 'color',
                    'value' => 'red',
                    'price' => 5.00,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Item added to cart successfully']);
        
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'attributes' => json_encode([
                [
                    'type' => 'color',
                    'value' => 'red',
                    'price' => 5.00,
                ],
            ]),
        ]);
    }

    public function test_can_update_item_in_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->price,
            'subtotal' => $this->product->price * 2,
        ]);

        $response = $this->putJson(route('carts.update-item', $cartItem->id), [
            'quantity' => 3,
            'variations' => [
                [
                    'type' => 'color',
                    'value' => 'blue',
                    'price' => 10.00,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Item updated successfully']);
        
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 3,
            'attributes' => json_encode([
                [
                    'type' => 'color',
                    'value' => 'blue',
                    'price' => 10.00,
                ],
            ]),
        ]);
    }

    public function test_can_delete_item_from_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $response = $this->deleteJson(route('carts.delete-item', $cartItem->id));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Item deleted successfully']);
        
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_can_delete_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->count(2)->create(['cart_id' => $cart->id]);

        $response = $this->deleteJson(route('carts.delete'));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cart deleted successfully']);
        
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_can_show_cart_items()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItems = CartItem::factory()->count(2)->create(['cart_id' => $cart->id]);

        $response = $this->getJson(route('carts.show-all-items'));

        $response->assertStatus(200)
            ->assertJson([
                'cart' => [
                    'id' => $cart->id,
                ],
                'cart_items' => $cartItems->toArray(),
            ]);
    }
}
