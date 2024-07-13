<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout()
    {
        $user = Auth::user();

        $cart = $user->cart()->with([
            'cartItems.product' => function ($query) {
                $query->with('category', 'tags', 'images', 'variations');
            }
        ])->first();

        if (!$cart) {
            return response()->json(['message' => 'Your cart is empty.'], 404);
        }

        return response()->json($cart, 200);
    }
}
