<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    
    protected $fillable = ['session_id', 'user_id', 'total_quantity', 'total_price', 'is_active', 'completed_at', 'discount_code', 'shipping_method', 'payment_status'];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Add calculateTotals method
    public function calculateTotals()
    {
        $totalQuantity = $this->cartItems->sum('quantity');
        $totalPrice = $this->cartItems->sum('subtotal');

        $this->update([
            'total_quantity' => $totalQuantity,
            'total_price' => $totalPrice,
        ]);
    }

}
