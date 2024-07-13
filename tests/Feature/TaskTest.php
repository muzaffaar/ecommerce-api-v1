<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $courier;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->courier = User::factory()->create(['role' => 'courier']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function test_admin_can_list_tasks()
    {
        $tasks = Task::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson(route('admin.tasks.index'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'tasks' => $tasks->toArray(),
            ]);
    }

    /** @test */
    public function test_admin_can_assign_courier_to_task()
    {
        $task = Task::factory()->create();

        $response = $this->actingAs($this->admin)->postJson(route('admin.tasks.assignCourier', $task->id), [
            'courier_id' => $this->courier->id,
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'courier_id' => $this->courier->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function test_admin_can_unassign_courier_from_task()
    {
        $courier = $this->courier;
        $task = Task::factory()->create(['courier_id' => $courier->id, 'status' => 'in_progress']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.tasks.unassignCourier', $task->id));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'courier_id' => null,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function test_admin_can_update_task_status()
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.tasks.updateStatus', $task->id), ['status' => 'completed']);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function test_admin_can_create_a_task()
    {
        $shippingAddress = ShippingAddress::factory()->create();
        $order = Order::factory()->create([
            'status' => 'pending',
            'shipping_address_id' => $shippingAddress->id,
        ]);

        $order->update(['status' => 'ready']);

        $this->assertDatabaseHas('tasks', [
            'order_id' => $order->id,
            'shipping_address_id' => $shippingAddress->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function test_courier_can_accept_task()
    {
        $courier = $this->courier;
        $task = Task::factory()->create(['status' => 'pending']);
        $response = $this->actingAs($courier)->postJson(route('courier.tasks.acceptTask', $task->id));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'courier_id' => $courier->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function test_courier_can_decline_task()
    {
        $courier = $this->courier;
        $task = Task::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($courier)->postJson(route('courier.tasks.declineTask', $task->id));
        $response->assertStatus(200)->assertJson(['status' => 'success']);
    }

    /** @test */
    public function test_admin_can_show_task()
    {
        $task = Task::factory()->create();

        $response = $this->actingAs($this->admin)->getJson(route('admin.tasks.show', $task->id));

        $response->assertStatus(200)
            ->assertJson($task->toArray());
    }

    /** @test */
    public function test_courier_can_complete_task()
    {
        $courier = $this->courier;
        $order = Order::factory()->create();
        $task = Task::factory()->create(['courier_id' => $courier->id, 'status' => 'in_progress', 'order_id' => $order->id]);

        $response = $this->actingAs($courier)->postJson(route('courier.tasks.completeTask', $task->id));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

}
