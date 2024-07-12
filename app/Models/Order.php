<?php

namespace App\Models;

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
        'cancelled'
    ];
}
