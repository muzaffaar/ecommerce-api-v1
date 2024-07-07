<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function seeMyOrders()
    {
        $user = auth()->user(); // Assuming you are using authentication and fetching orders for the authenticated user
        
        $orders = Order::where('user_id', $user->id)
            ->with('items', 'billingAddress', 'shippingAddress')
            ->get();

        return response()->json(['orders' => $orders]);
    }
}
