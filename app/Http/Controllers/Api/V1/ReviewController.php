<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $orderItemId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $orderItemId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $orderItem = OrderItem::findOrFail($orderItemId);

        // Check if the authenticated user has permission to review this order item
        if (Auth::id() !== $orderItem->order->user_id) {
            return response()->json(['error' => 'You are not authorized to review this product'], 403);
        }

        // Check if a review already exists for this order item
        if ($orderItem->review) {
            return response()->json(['error' => 'A review already exists for this order item'], 400);
        }

        $review = new Review([
            'user_id' => Auth::id(),
            'product_id' => $orderItem->product_id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        $review->save();

        return response()->json(['message' => 'Review added successfully', 'review' => $review]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $review = Review::findOrFail($id);
        return response()->json(['review' => $review]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
            'is_approved' => 'nullable|boolean',
        ]);

        $review = Review::findOrFail($id);

        // Check if the authenticated user has permission to update this review
        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'You are not authorized to update this review'], 403);
        }

        $review->rating = $request->rating;
        $review->review = $request->review;
        
        // Admin can update review status
        if ($request->has('is_approved')) {
            $review->is_approved = $request->is_approved;
        }

        $review->save();

        return response()->json(['message' => 'Review updated successfully', 'review' => $review]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Check if the authenticated user has permission to delete this review
        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'You are not authorized to delete this review'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
