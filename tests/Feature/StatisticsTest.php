<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();
        
        User::factory()->count(10)->create();
        Product::factory()->count(5)->create();
        $orders = Order::factory()->count(20)->create();
        
        foreach ($orders as $order) {
            OrderItem::factory()->count(3)->create(['order_id' => $order->id]);
            Payment::factory()->create(['order_id' => $order->id]);
        }

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    
    public function test_it_returns_statistics()
    {
        $response = $this->actingAs($this->admin)->getJson(route('admin.dashboard'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_sales',
                     'total_orders',
                     'total_users',
                     'top_selling_products' => [
                         '*' => [
                             'name',
                             'total_quantity'
                         ]
                     ],
                     'monthly_sales' => [
                         '*' => [
                             'year',
                             'month',
                             'total'
                         ]
                     ],
                     'average_order_value',
                     'payment_statuses' => [
                         '*' => [
                             'status',
                             'count'
                         ]
                     ],
                     'user_roles' => [
                         '*' => [
                             'role',
                             'count'
                         ]
                     ],
                     'monthly_new_users' => [
                         '*' => [
                             'year',
                             'month',
                             'count'
                         ]
                     ],
                 ]);
    }

}
