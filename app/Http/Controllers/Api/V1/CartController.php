<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function cartAddItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Calculate the total variation price
        $variationTotalPrice = 0;
        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                $variationTotalPrice += $variation['price'];
            }
        }

        if (Auth::check()) {
            $user = auth()->user();
            $cart = $user->cart()->firstOrCreate([]);

            $cartItem = $cart->cartItems()->where('product_id', $request->product_id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $request->quantity;
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'subtotal' => ($cartItem->price + $variationTotalPrice) * $newQuantity,
                ]);
            } else {
                $product = Product::findOrFail($request->product_id);
                $cartItem = new CartItem([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'price' => $product->price,
                    'subtotal' => ($product->price + $variationTotalPrice) * $request->quantity,
                    'attributes' => json_encode($request->variations),
                    'is_gift' => $request->is_gift ?? false,
                    'notes' => $request->notes,
                    'custom_fields' => json_encode($request->custom_fields),
                ]);
                $cart->cartItems()->save($cartItem);
            }

            return response()->json(['message' => 'Authenticated user added item to cart', 'cart_items' => $cart->cartItems]);
        } else {
            $cartItems = session()->get('cart.items', []);

            $key = $request->product_id;
            if (isset($cartItems[$key])) {
                $cartItems[$key]['quantity'] += $request->quantity;
                $cartItems[$key]['subtotal'] = ($cartItems[$key]['price'] + $variationTotalPrice) * $cartItems[$key]['quantity'];
            } else {
                $product = Product::findOrFail($request->product_id);
                $cartItems[$key] = [
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'price' => $product->price,
                    'subtotal' => ($product->price + $variationTotalPrice) * $request->quantity,
                    'attributes' => json_encode($request->variations),
                    'is_gift' => $request->is_gift ?? false,
                    'notes' => $request->notes,
                    'custom_fields' => json_encode($request->custom_fields),
                ];
            }

            session()->put('cart.items', $cartItems);

            return response()->json(['message' => 'Item added to session cart', 'cart_items' => $cartItems]);
        }
    }



    /**
     * Delete item from the cart.
     */
    public function cartDeleteItem(Request $request)
    {
        
        // Determine cart based on user authentication
        if (Auth::check()) {
            $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
            ]);
            // Authenticated user
            $user = Auth::user();
            $cartItem = CartItem::where('id', $request->cart_item_id)
                                ->whereHas('cart', function ($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->firstOrFail();
        } else {
            $request->validate([
                'cart_item_id' => 'required',
            ]);
            // Guest user
            $cartItems = session()->get('cart.items', []);

            // Remove the cart item from session
            unset($cartItems[$request->cart_item_id]);
            session()->put('cart.items', $cartItems);

            return response()->json(['message' => 'Cart item deleted successfully']);
        }

        // Delete the cart item for authenticated users
        $cartItem->delete();

        return response()->json(['message' => 'Cart item deleted successfully']);
    }

    /**
     * Update item in the cart.
     */
    public function cartUpdateItem(Request $request)
    {
        
        // Determine cart based on user authentication
        if (Auth::check()) {
            $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
                'quantity' => 'required|integer|min:1',
            ]);
            // Authenticated user
            $user = Auth::user();
            $cartItems = CartItem::where('id', $request->cart_item_id)
                                ->whereHas('cart', function ($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->firstOrFail();

            $cartItems->update([
                'quantity' => $request->quantity,
                'subtotal' => $cartItems->price * $request->quantity, // Update subtotal
            ]);

        } else {
            $request->validate([
                'cart_item_id' => 'required',
                'quantity' => 'required|integer|min:1',
            ]);
            // Guest user scenario (simulating session)
            $cartItems = session()->get('cart.items', []);
            // dd($cartItems);
            // return response()->json($cartItems);
    
            // Check if the cart item exists in session
            if (!isset($cartItems[$request->cart_item_id])) {
                return response()->json([
                    'message' => 'The selected cart item id is invalid.',
                    'errors' => [
                        'cart_item_id' => ['The selected cart item id is invalid.'],
                    ],
                ], 422);
            }
    
            // Update quantity and subtotal
            $cartItems[$request->cart_item_id]['quantity'] = $request->quantity;
            $cartItems[$request->cart_item_id]['subtotal'] = $cartItems[$request->cart_item_id]['price'] * $request->quantity;
    
            // Update attributes if provided
            if ($request->attributes) {
                $cartItems[$request->cart_item_id]['attributes'] = json_encode($request->attributes);
            }
    
            // Update session with modified cart items
            session()->put('cart.items', $cartItems);
    
            return response()->json([
                'message' => 'Cart item updated successfully',
                'cart_item' => $cartItems[$request->cart_item_id],
            ]);
        }

        return response()->json(['message' => 'Cart item updated successfully', 'cart_items' => $cartItems]);
    }

    /**
     * Show all items in the cart.
     */
    public function cartShowAllItems(Request $request)
    {
        // Determine cart based on user authentication
        if (Auth::check()) {
            // Authenticated user
            $user = Auth::user();
            $cart = $user->cart->with('cartItems.product')->first();

            // Transfer session cart items to database if any
            $this->transferSessionCartToDatabase($user);

            // Refresh cart object after transfer
            $cart = $user->cart->with('cartItems.product')->first();

            if ($cart) {
                $cart->cartItems->transform(function ($cartItem) {
                    if (is_string($cartItem->attributes)) {
                        $cartItem->attributes = json_decode($cartItem->attributes, true);
                    }
                    return $cartItem;
                });
            }
        } else {
            // Guest user
            $cartItems = session()->get('cart.items', []);

            // Retrieve products for cart items
            $productIds = collect($cartItems)->pluck('product_id')->toArray();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // Prepare cart items with product information
            $cartItemsWithProducts = [];
            foreach ($cartItems as $key => $item) {
                if (isset($products[$item['product_id']])) {
                    $product = $products[$item['product_id']];
                    // Decode attributes if they are JSON-encoded
                    $attributes = $item['attributes'] ?? [];
                    if (is_string($attributes)) {
                        $attributes = json_decode($attributes, true);
                    }
                    $cartItemsWithProducts[] = [
                        'id' => $key,
                        'product' => $product->toArray(), // Convert product to array
                        'quantity' => $item['quantity'],
                        'price' => $product->price, // Assuming price is stored in the product table
                        'subtotal' => $product->price * $item['quantity'], // Calculate subtotal
                        'attributes' => $attributes
                    ];
                }
            }

            return response()->json(['cart_items' => $cartItemsWithProducts]);

        }

        // Return response with cart items for authenticated users
        return response()->json(['cart_items' => $cart ? $cart->cartItems : []]);
        // $order = Order::where('order_number', '3f94c648-1fda-473b-8105-43b6fa3283bc')->firstOrFail();
        // return response()->json($order->user->email);
    }

    /**
     * Helper function to transfer session cart items to database for authenticated users.
     */
    public function transferSessionCartToDatabase($user)
    {
        $cartItems = session()->get('cart.items', []);

        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                // Check if the product already exists in the cart
                $existingCartItem = $user->cart()->whereHas('cartItems', function ($query) use ($item) {
                    $query->where('product_id', $item['product_id']);
                })->first();

                if ($existingCartItem) {
                    // Update existing cart item quantity and subtotal
                    $existingCartItem->cartItems()->updateOrCreate(
                        ['product_id' => $item['product_id']],
                        [
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'subtotal' => $item['price'] * $item['quantity'],
                            // Add other fields as needed
                        ]
                    );
                } else {    
                    // Add new cart item to the user's cart
                    $product = Product::findOrFail($item['product_id']);
                    $cartItem = new CartItem([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'subtotal' => $product->price * $item['quantity'],
                        // Add other fields as needed
                    ]);

                    $cart = $user->cart()->firstOrCreate([]);
                    $cart->cartItems()->save($cartItem);
                }
            }

            // Clear session cart items after transfer
            session()->forget('cart.items');
        }
    }
}
