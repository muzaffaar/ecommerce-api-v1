<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'status',
        // Add more fields as needed
    ];

    // Define relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
}
