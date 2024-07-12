<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskCreated;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function assignCourier(Request $request, Task $task)
    {
        $courier = User::find($request->courier_id);

        if ($courier && $courier->role == 'courier') {
            $task->courier_id = $courier->id;
            $task->status = 'in_progress';  // Update status to in_progress when assigned
            $task->save();
            $courier->notify(new TaskAssigned($task));
        }

        return response()->json(['status' => 'success']);
    }

    public function unassignCourier(Task $task)
    {
        $task->courier_id = null;
        $task->status = 'pending';  // Update status to pending when unassigned
        $task->save();

        return response()->json(['status' => 'success']);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $status = $request->status;

        if (in_array($status, Task::$statuses)) {
            $task->status = $status;
            $task->save();
        }

        return response()->json(['status' => 'success']);
    }

    public function createTask(Request $request)
    {
        // Validate and create the task
        $task = Task::create([
            'order_id' => $request->order_id,
            'shipping_address_id' => $request->shipping_address_id,
            'status' => 'pending',
            // Other fields...
        ]);

        // Notify all free couriers
        $freeCouriers = User::where('role', 'courier')->whereDoesntHave('tasks', function($query) {
            $query->where('status', 'in_progress');
        })->get();

        foreach ($freeCouriers as $courier) {
            $courier->notify(new TaskCreated($task));
        }

        return response()->json(['status' => 'success', 'task' => $task]);
    }

    public function acceptTask(Request $request, Task $task)
    {
        $courier = auth()->user();

        if ($courier->role != 'courier') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($task->status == 'pending' && $task->courier_id === null) {
            $task->courier_id = $courier->id;
            $task->status = 'in_progress';
            $task->save();

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Task not available'], 400);
    }

    public function declineTask(Request $request, Task $task)
    {
        $courier = auth()->user();

        if ($courier->role != 'courier') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Mark task as declined for this courier
        // In this example, we assume the courier will not receive notifications again for this task.
        // Implement your own logic if you want to keep track of declined tasks

        return response()->json(['status' => 'success']);
    }

    public function show(Task $task)
    {
        return response()->json($task);
    }

    public function completeTask(Request $request, Task $task)
    {
        $courier = auth()->user();

        if ($courier->role != 'courier') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($task->status == 'in_progress' && $task->courier_id == $courier->id) {
            $task->status = 'completed';
            $task->save();

            // Update the order status
            $task->order->updateStatusBasedOnTasks();

            // Optionally, notify the admin or relevant stakeholders
            // $admin->notify(new TaskCompleted($task)); // Create a TaskCompleted notification if needed

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Task not available or already completed'], 400);
    }

}
