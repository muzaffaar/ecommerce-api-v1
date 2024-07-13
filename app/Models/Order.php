<?php

namespace App\Models;

use App\Notifications\TaskCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'total_amount', 'status', 'user_id', 'billing_address_id', 'shipping_address_id'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(BillingAddress::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            if ($order->isDirty('status') && $order->status == 'ready') {
                $order->createTask();
            }
        });
    }

    public function createTask()
    {
        $task = Task::create([
            'order_id' => $this->id,
            'shipping_address_id' => $this->shipping_address_id,
            'status' => 'pending',
        ]);

        $freeCouriers = User::where('role', 'courier')->whereDoesntHave('tasks', function($query) {
            $query->where('status', 'in_progress');
        })->get();

        foreach ($freeCouriers as $courier) {
            $courier->notify(new TaskCreated($task));
        }
    }

    public function updateStatusBasedOnTasks()
    {
        $taskStatuses = $this->tasks->pluck('status')->unique();

        if ($taskStatuses->contains('in_progress')) {
            $this->status = 'in_progress';
        } elseif ($taskStatuses->contains('pending')) {
            $this->status = 'pending';
        } elseif ($taskStatuses->contains('cancelled')) {
            $this->status = 'cancelled';
        } elseif ($taskStatuses->every(fn($status) => $status == 'completed')) {
            $this->status = 'completed';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }

    public static $statuses = [
        'pending',
        'in_progress',
        'completed',
        'cancelled',
        'ready'
    ];
}
