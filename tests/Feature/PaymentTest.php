<?php

namespace Tests\Feature;

use App\Models\BillingAddress;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;


class PaymentTest extends TestCase
{
    // use RefreshDatabase;

    protected $user;
    protected $product;
    protected $cart;
    protected $cartItems;
    protected $billingAddress;
    protected $shippingAddress;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['password' => bcrypt('123123123')]);
        $this->product = Product::factory()->create();
        $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $this->cartItems = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $this->product->id,
            'price' => 5
        ]);

        $this->billingAddress = BillingAddress::factory()->make()->toArray();
        $this->shippingAddress = ShippingAddress::factory()->make()->toArray();
    }

    public function test_payment_by_entering_returned_link()
    {

        $data = [
                "billing_address" => [
                    "first_name" => "John",
                    "last_name" => "Doe",
                    "address" => "123 Billing St",
                    "city" => "Billing City",
                    "state" => "Billing State",
                    "postal_code" => "12345",
                    "country" => "Billing Country",
                    "phone" => "123-456-7890"
                ],
                
                "shipping_address" => [
                    "first_name" => "Jane",
                    "last_name" => "Doe",
                    "address" => "456 Shipping St",
                    "city" => "Shipping City",
                    "state" => "Shipping State",
                    "postal_code" => "54321",
                    "country" => "Shipping Country",
                    "phone" => "987-654-3210"
                ]
        ];

        $response = $this->actingAs($this->user)->postJson(route('payment'), $data);

        $link = $response->json();

        File::put(storage_path('app/paypal_link.txt'), $link);

        $this->assertTrue(File::exists(storage_path('app/paypal_link.txt')));
        $this->assertEquals($link, File::get(storage_path('app/paypal_link.txt')));
    }

    public function test_dusk_command()
    {
        $exitCode = Artisan::call('payment');
        sleep(20);
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode, "Dusk tests did not pass. Output: $output");

        if ($exitCode === 0) {
            $this->test_payment_success();
        }
    }

    protected function test_payment_success()
    {
        
        session()->put('billing_address', $this->billingAddress);
        session()->put('shipping_address', $this->shippingAddress);

        $capturedLink = File::get(storage_path('app/paypal_success.txt'));

        $queryParams = [];
        parse_str(parse_url($capturedLink, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'] ?? '';
        $payerID = $queryParams['PayerID'] ?? '';

        $response = $this->actingAs($this->user)->getJson(route('success', ['token' => $token, 'PayerID' => $payerID]));
        dump($response->getContent());
        $response->assertStatus(200);
        // $response->assertJson(['success' => 'Transaction complete and Order has been created successfully.']);

        // $order = Order::where('status', 'completed')
        //               ->orderBy('created_at', 'desc')
        //               ->skip(0)
        //               ->first();
        // $this->assertNotNull($order);
        // $this->assertEquals($user->id, $order->user_id);

        // $this->assertEquals($order->billing_address_id, BillingAddress::where('user_id', $user->id)->first()->id);
        // $this->assertEquals($order->shipping_address_id, ShippingAddress::where('user_id', $user->id)->first()->id);

    }

    public function test_failed_payment()
    {
        $response = $this->actingAs($this->user)->getJson(route('cancel'));
        $response->assertStatus(200);
        $response->assertJson(['cancel' => 'Transaction has been cancelled.']);
    }
}
