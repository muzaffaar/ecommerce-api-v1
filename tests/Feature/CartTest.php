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

    protected $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonAdminUser = User::factory()->create([
            'role' => 'user',
            'phone_verified_at' => now(),
        ]);
    }

    public function test_authenticated_user_can_add_cart_items()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->nonAdminUser)
                        ->postJson(route('carts.add-item'), [
                            'product_id' => $product->id,
                            'quantity' => 2
                        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Item added to cart successfully',
                 ]);
    }

    public function test_guest_can_add_cart_items()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->postJson(route('carts.add-item'), [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Item added to cart successfully',
                 ]);
    }

    public function test_authenticated_user_can_delete_cart_items()
    {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->nonAdminUser->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->nonAdminUser)
                        ->deleteJson(route('carts.delete-item'), [
                            'cart_item_id' => $cartItem->id,
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Cart item deleted successfully',
                ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_guest_can_delete_cart_items()
    {
        $product = Product::factory()->create();

        // Simulate guest user scenario (simulating session)
        $cartItems = [
            '1' => [
                'product_id' => $product->id,
                'quantity' => 2, // Initial quantity
                'price' => $product->price, // Ensure price is set in factory
                // Add other fields as needed
            ]
        ];

        // Store cart items in session
        session()->put('cart.items', $cartItems);

        // Delete request for guest user scenario
        $responseGuest = $this->deleteJson(route('carts.delete-item'), [
            'cart_item_id' => '1', // Using the same key as in session
        ]);

        // Assert response for guest user
        $responseGuest->assertStatus(200)
                      ->assertJson([
                          'message' => 'Cart item deleted successfully',
                      ]);

        // Assert that the cart item is deleted from session for guest user
        $updatedCartItemsSession = session()->get('cart.items');
        $this->assertArrayNotHasKey('1', $updatedCartItemsSession);
    }

    public function test_authenticated_user_can_update_cart_items()
    {
        $product = Product::factory()->create();
    
        $cart = Cart::factory()->create(['user_id' => $this->nonAdminUser->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);
    
        $newQuantity = 5;
        $response = $this->actingAs($this->nonAdminUser)
                         ->putJson(route('carts.update-item'), [
                             'cart_item_id' => $cartItem->id,
                             'quantity' => $newQuantity,
                         ]);
    
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Cart item updated successfully',
                 ]);
    
        $updatedCartItem = CartItem::find($cartItem->id);
    
        $this->assertEquals($newQuantity, $updatedCartItem->quantity);
        $this->assertEqualsWithDelta($product->price * $newQuantity, $updatedCartItem->subtotal, 0.01);
    
    }
    
    public function test_guest_can_update_cart_items()
    {
        $product = Product::factory()->create();
    
        $cartItems = [
            '1' => [
                'product_id' => $product->id,
                'quantity' => 2, // Initial quantity
                'price' => $product->price, // Ensure price is set in factory
            ]
        ];
    
        session()->put('cart.items', $cartItems);
        // dd(session()->get('cart.items'));
        $newQuantityGuest = 3;
        $responseGuest = $this->putJson(route('carts.update-item'), [
            'cart_item_id' => '1',
            'quantity' => $newQuantityGuest,
        ]);
    
        $responseGuest->assertStatus(200)
                    ->assertJson([
                        'message' => 'Cart item updated successfully',
                    ]);
    
        $updatedCartItemsSession = session()->get('cart.items');
        $this->assertEquals($newQuantityGuest, $updatedCartItemsSession['1']['quantity']);
        $this->assertEqualsWithDelta($product->price * $newQuantityGuest, $updatedCartItemsSession['1']['subtotal'], 0.01);
    }

    public function test_authenticated_user_can_see_all_cart_items()
    {
        $response = $this->actingAs($this->nonAdminUser)
                        ->getJson(route('carts.show-all-items'));
    
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'cart_items' => [],
                ]);
    }
    public function test_guest_can_see_all_cart_items()
    {
        $response = $this->getJson(route('carts.show-all-items'));
    
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'cart_items' => [],
                ]);
    }

    public function test_transfer_cart_from_session_to_database()
    {
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 15.00]);

        // Simulate cart items in session
        $cartItems = [
            [
                'product_id' => $product1->id,
                'quantity' => 2,
                'price' => $product1->price,
                // Add other fields as needed
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 3,
                'price' => $product2->price,
                // Add other fields as needed
            ],
        ];

        // Store cart items in session
        session()->put('cart.items', $cartItems);

        // Instantiate the CartController
        $cartController = new CartController();

        // Call the method to transfer session cart to database
        $cartController->transferSessionCartToDatabase($this->nonAdminUser);

        // Assert that cart items are transferred to the database
        foreach ($cartItems as $item) {
            $this->assertDatabaseHas('cart_items', [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                // Add other fields as needed
            ]);
        }

        // Assert that session cart items are cleared after transfer
        $this->assertEmpty(session()->get('cart.items'));
    }
}
