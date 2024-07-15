<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function(){
    Route::middleware(['throttle:6,1'])->group(function(){
        /* Phone verification */
        Route::get('/phone-verification', [PhoneVerificationController::class, 'sendVerificationCode'])->name('phone.verification.notice');
        
        Route::post('/phone-verification-resend', [PhoneVerificationController::class, 'resendVerificationCode'])->name('phone.verification.resend');
        
        Route::post('/phone-verify', [PhoneVerificationController::class, 'verifyCode'])->name('phone.verify');
        
        /* Email verification */
        Route::get('/email/verify', [EmailVerificationController::class, 'index'])->name('verification.notice');
        
        Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');
        
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    });
    
    Route::middleware(['verified', 'phone.verified'])->group(function (){

        Route::post('/payment', [PaymentController::class, 'payment'])->name('payment');

        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        
        Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
        
        Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');

        Route::post('/coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
    });

    Route::post('order-items/{orderItemId}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::put('reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});