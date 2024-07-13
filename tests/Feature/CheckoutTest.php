<?php

namespace Tests\Feature;


use App\Http\Controllers\Api\V1\CheckoutController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'user',
            'phone_verified_at' => now(),
        ]);        
    }

    public function test_checkout_with_cart()
    {
        $user = User::factory()->create(['phone_verified_at' => now()]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $cartItems = CartItem::factory()->count(2)->create(['cart_id' => $cart->id]);

        // Mock Auth::user() to return the user
        $this->actingAs($user);

        // Hit the checkout endpoint
        $response = $this->getJson(route('checkout'));

        // Assert response
        $response->assertStatus(200);

        // If the cart is not empty, assert the JSON structure
        if ($cartItems->isNotEmpty()) {
            $expectedJsonStructure = [
                'id',
                'user_id',
                'cart_items' => [
                    '*' => [
                        'id',
                        'cart_id',
                        'product' => [
                            'id',
                            'category',
                            'tags',
                            'images',
                            'variations',
                            // Adjust fields as needed
                        ],
                        'quantity',
                        'price',
                        'subtotal',
                        // Add other fields as needed
                    ],
                ],
            ];

            $response->assertJsonStructure($expectedJsonStructure);
        }
    }


    /**
     * Test checkout when user does not have a cart.
     *
     * @return void
     */
    public function test_checkout_without_cart()
    {
        $this->actingAs($this->user);

        // Hit the checkout endpoint
        $response = $this->getJson(route('checkout'));

        // Assert response
        $response->assertStatus(404)
            ->assertJson(['message' => 'Your cart is empty.']);
    }
}
