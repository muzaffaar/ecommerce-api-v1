<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\CreateCouponRequest;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::where('code', $request->code)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon'], 400);
        }

        $user = $request->user();
        if (!$coupon->is_common && !$coupon->users->contains($user)) {
            return response()->json(['message' => 'This coupon is not available for you'], 403);
        }

        $discount = 0;
        if ($coupon->type == 'fixed') {
            $discount = $coupon->discount;
        } elseif ($coupon->type == 'percent') {
            $discount = ($coupon->discount / 100) * $request->total;
        }

        $newTotal = $request->total - $discount;

        return response()->json([
            'message' => 'Coupon applied successfully',
            'discount' => $discount,
            'new_total' => $newTotal,
        ]);
    }

    public function createCoupon(CreateCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create([
            'code' => $request->code,
            'discount' => $request->discount,
            'type' => $request->type,
            'is_common' => $request->is_common ?? false,
            'expires_at' => $request->expires_at,
        ]);

        return response()->json($coupon, 201);
    }

    public function assignCouponToUser(Request $request, User $user): JsonResponse
    {
        $coupon = Coupon::where('code', $request->code)->first();

        if ($coupon->is_common) {
            return response()->json(['message' => 'This coupon is available for all users.'], 400);
        }

        $user->coupons()->syncWithoutDetaching($coupon->id);

        return response()->json(['message' => 'Coupon assigned successfully'], 200);
    }
}
