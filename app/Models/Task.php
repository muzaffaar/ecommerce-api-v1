<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'courier_id', 'shipping_address_id', 'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public static $statuses = [
        'pending',
        'in_progress',
        'completed',
        'cancelled'
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($task) {
            if ($task->isDirty('status')) {
                $task->order->updateStatusBasedOnTasks();
            }
        });
    }
}
