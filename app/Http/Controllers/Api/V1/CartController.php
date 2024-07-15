<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function cartAddItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variations' => 'array',
            'variations.*.type' => 'required_with:variations|string',
            'variations.*.value' => 'required_with:variations|string',
            'variations.*.price' => 'required_with:variations|numeric',
        ]);

        $user = Auth::check() ? auth()->user() : null;
        $session_id = session()->getId();
        $cart = Cart::firstOrCreate(['session_id' => $session_id], ['user_id' => $user ? $user->id : null]);

        $product = Product::findOrFail($request->product_id);
        $price = $product->price;

        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                $price += $variation['price'];
            }
        }

        $cartItem = $cart->cartItems()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'quantity' => $request->quantity,
                'price' => $price,
                'subtotal' => $price * $request->quantity,
                'attributes' => json_encode($request->variations),
                'is_gift' => $request->is_gift ?? false,
                'notes' => $request->notes,
                'custom_fields' => json_encode($request->custom_fields),
            ]
        );

        $cart->calculateTotals();

        return response()->json(['message' => 'Item added to cart successfully', 'cart_items' => $cart->cartItems]);
    }

    public function cartUpdateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'variations' => 'array',
            'variations.*.type' => 'required_with:variations|string',
            'variations.*.value' => 'required_with:variations|string',
            'variations.*.price' => 'required_with:variations|numeric',
        ]);

        $cartItem = CartItem::findOrFail($itemId);
        $product = $cartItem->product;
        $price = $product->price;

        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                $price += $variation['price'];
            }
        }

        $cartItem->update([
            'quantity' => $request->quantity,
            'price' => $price,
            'subtotal' => $price * $request->quantity,
            'attributes' => json_encode($request->variations),
            'is_gift' => $request->is_gift ?? false,
            'notes' => $request->notes,
            'custom_fields' => json_encode($request->custom_fields),
        ]);

        $cartItem->cart->calculateTotals();

        return response()->json(['message' => 'Item updated successfully', 'cart_item' => $cartItem]);
    }

    public function cartDeleteItem($itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);
        $cart = $cartItem->cart;

        $cartItem->delete();
        $cart->calculateTotals();

        return response()->json(['message' => 'Item deleted successfully']);
    }

    public function cartDelete()
    {
        $user = Auth::check() ? auth()->user() : null;
        $session_id = session()->getId();
        $cart = Cart::where('session_id', $session_id)->orWhere('user_id', $user ? $user->id : null)->first();

        if ($cart) {
            $cart->cartItems()->delete();
            $cart->delete();
        }

        return response()->json(['message' => 'Cart deleted successfully']);
    }

    public function cartShow()
    {
        $user = Auth::check() ? auth()->user() : null;
        $session_id = session()->getId();
        $cart = Cart::with('cartItems')->where('session_id', $session_id)->orWhere('user_id', $user ? $user->id : null)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }

        return response()->json(['cart' => $cart, 'cart_items' => $cart->cartItems]);
    }
}