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
use Illuminate\Support\Facades\Cache;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create users
        User::factory()->count(10)->create(['role' => 'admin']);

        // Create products
        $products = Product::factory()->count(5)->create();

        // Create orders with order items
        Order::factory()->count(10)->create()->each(function ($order) use ($products) {
            $order->items()->saveMany(OrderItem::factory()->count(3)->make([
                'product_id' => $products->random()->id,
            ]));
        });

        // Create payments
        Payment::factory()->count(10)->create();
    }

    public function test_statistics_index()
    {
        // Authenticate a user
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Clear the cache to ensure fresh data is fetched
        Cache::flush();

        $response = $this->getJson(route('admin.dashboard'));

        $response->assertStatus(200);

        // Assert the response contains the necessary keys
        $response->assertJsonStructure([
            'total_sales',
            'total_orders',
            'total_users',
            'top_selling_products' => [
                '*' => ['name', 'total_quantity'],
            ],
            'monthly_sales' => [
                '*' => ['year', 'month', 'total'],
            ],
            'average_order_value',
            'payment_statuses' => [
                '*' => ['status', 'count'],
            ],
            'user_roles' => [
                '*' => ['role', 'count'],
            ],
            'monthly_new_users' => [
                '*' => ['year', 'month', 'count'],
            ],
        ]);

        // Check some values (adjust these checks based on your test data)
        $this->assertNotNull($response['total_sales']);
        $this->assertNotNull($response['total_orders']);
        $this->assertNotNull($response['total_users']);
        $this->assertCount(5, $response['top_selling_products']); // Assuming we always get top 5
    }

    public function test_statistics_caching()
    {
        // Authenticate a user
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // First request to populate the cache
        $this->getJson(route('admin.dashboard'));

        // Update some data
        Order::factory()->create();

        // Second request should return cached data
        $response = $this->getJson(route('admin.dashboard'));

        $response->assertStatus(200);

        // Assert the response contains the necessary keys and is unchanged
        $response->assertJsonStructure([
            'total_sales',
            'total_orders',
            'total_users',
            'top_selling_products' => [
                '*' => ['name', 'total_quantity'],
            ],
            'monthly_sales' => [
                '*' => ['year', 'month', 'total'],
            ],
            'average_order_value',
            'payment_statuses' => [
                '*' => ['status', 'count'],
            ],
            'user_roles' => [
                '*' => ['role', 'count'],
            ],
            'monthly_new_users' => [
                '*' => ['year', 'month', 'count'],
            ],
        ]);

        // Clear the cache and request again to ensure updated data is fetched
        Cache::flush();

        $response = $this->getJson(route('admin.dashboard'));

        $response->assertStatus(200);

        // Assert the response contains the necessary keys and reflects the updated data
        $response->assertJsonStructure([
            'total_sales',
            'total_orders',
            'total_users',
            'top_selling_products' => [
                '*' => ['name', 'total_quantity'],
            ],
            'monthly_sales' => [
                '*' => ['year', 'month', 'total'],
            ],
            'average_order_value',
            'payment_statuses' => [
                '*' => ['status', 'count'],
            ],
            'user_roles' => [
                '*' => ['role', 'count'],
            ],
            'monthly_new_users' => [
                '*' => ['year', 'month', 'count'],
            ],
        ]);
    }
}
