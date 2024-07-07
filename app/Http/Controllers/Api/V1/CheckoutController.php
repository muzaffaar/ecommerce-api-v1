<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout()
    {
        // Retrieve all data from cart and cart items
        // Ask adresses from customer in frontend

        // Assuming the user is authenticated
        $user = Auth::user();

        // Eager load the user's cart, cart items, and the associated product details
        $cart = $user->cart()->with([
            'cartItems.product' => function ($query) {
                $query->with('category', 'tags', 'images', 'variations');
            }
        ])->first();

        // Check if the cart is null and handle it accordingly
        if (!$cart) {
            return response()->json(['message' => 'Your cart is empty.'], 404);
        }

        // Return the cart data as JSON
        return response()->json($cart, 200);
    }
}
