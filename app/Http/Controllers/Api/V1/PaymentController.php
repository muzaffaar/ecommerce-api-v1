<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Jobs\SendInvoice;
use App\Jobs\SendOrderConfirmation;
use App\Models\BillingAddress;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $provider;
    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));
        $paypalToken = $this->provider->getAccessToken();
        $this->provider->setAccessToken($paypalToken);
    }
    
    public function payment(AddressRequest $request)
    {
        $cart = Auth::user()->cart;  // Retrieve the user's cart
        if (!$cart) {
            // return redirect()->back()->with('error', 'Your cart is empty');
            return response()->json(['error' => 'Your cart is empty']);
        }
        
        $cartItems = $cart->cartItems;  // Retrieve cart items from the cart relationship
        if ($cartItems->isEmpty()) {
            // return redirect()->back()->with('error', 'Your cart is empty');
            return response()->json(['error' => 'Your cart is empty']);
        }

        
        $totalAmount = $cartItems->sum(function($item) {
            $price = $item->product->price;
            
            // Check if attributes exist and decode if it's a JSON string
            if (!empty($item->attributes)) {
                $item->attributes = json_decode($item->attributes, true);
                if (isset($item->attributes[0]['price'])) {
                    $price = $item->attributes[0]['price'];
                }else{
                    Log::error($item->attributes[0]);
                }
            }
        
            return $item->quantity * $price;
        });


        // Create or update billing address for the user
        $billingAddressModel = BillingAddress::updateOrCreate(
            ['user_id' => Auth::user()->id],
            $request->input('billing_address')
        );

        // Create or update shipping address for the user
        $shippingAddressModel = ShippingAddress::updateOrCreate(
            ['user_id' => Auth::user()->id],
            $request->input('shipping_address')
        );
        

        // Create a new order with a unique order number
        $order = Order::create([
            'user_id' => Auth::user()->id,
            'order_number' => Str::uuid(),
            'total_amount' => $totalAmount,
            'billing_address' => $billingAddressModel->id,
            'shipping_address' => $shippingAddressModel->id,
            'status' => 'pending',
        ]);

        // Store billing and shipping addresses temporarily in session or cache
        // session()->put('billing_address', $request->input('billing_address'));
        // session()->put('shipping_address', $request->input('shipping_address'));

        $response = $this->provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $totalAmount
                    ],
                    "reference_id" => $order->order_number
                ]
            ],
            "application_context" => [
                "return_url" => route('success'),
                "cancel_url" => route('cancel')
            ]
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json($link['href']);
                }
            }
        } else {
            return response()->json(['error' => $response['message'] ?? 'Something went wrong.']);
        }
    }

    public function success(Request $request)
    {
        $response = $this->provider->capturePaymentOrder($request['token']);
        dump($response);
     
        if (isset($response['status']) && ctype_upper($response['status']) == 'COMPLETED') {

            $billingAddress = session()->get('billing_address');
            $shippingAddress = session()->get('shipping_address');


            $orderId = $response['purchase_units'][0]['reference_id'];

            $order = Order::where('order_number', $orderId)->firstOrFail();
            $order->status = 'completed';
            $order->save();

            $cartItems = Auth::user()->cart->cartItems;

            foreach ($cartItems as $item) {
                $price = $item->product->price; // Default to product price

                if (!empty($item->attributes)) {
                    $attributes = is_string($item->attributes) ? json_decode($item->attributes, true) : $item->attributes;
                    if (isset($attributes['price'])) {
                        $price = $attributes['price'];
                    }
                }
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variation_id' => $item->variation_id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_id' => $response['id'],
                'status' => $response['status']
            ]);

            $cartItems->each->delete();

            SendOrderConfirmation::dispatch($order);
            SendInvoice::dispatch($order);

            return response()->json(['success' => 'Transaction complete and Order has been created successfully.']);
        } else {
            return response()->json(['error' => $response['message'] ?? 'Something went wrong.']);
        }
    }

    public function cancel()
    {
        return response()->json(['cancel' => 'Transaction has been cancelled.']);
    }
}
