<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware(['guest'])->group(function(){
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('categories', [CategoryController::class ,'index'])->name('categories.index');
Route::get('categories/{slug}', [CategoryController::class ,'show'])->name('categories.show');

/* Searching */ 
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

/* Products */ 
Route::get('products', [ProductController::class ,'index'])->name('products.index');
Route::get('products/{slug}', [ProductController::class ,'show'])->name('products.show');


/* Cart */
Route::prefix('carts')->group(function () {
    Route::post('/add-item', [CartController::class, 'cartAddItem'])->name('carts.add-item');
    Route::delete('/delete-item', [CartController::class, 'cartDeleteItem'])->name('carts.delete-item');
    Route::put('/update-item', [CartController::class, 'cartUpdateItem'])->name('carts.update-item');
    Route::get('/show-all-items', [CartController::class, 'cartShowAllItems'])->name('carts.show-all-items');
});

Route::middleware(['auth:sanctum'])->group(function(){
    
    Route::middleware(['can:admin'])->group(function(){
        Route::post('products', [ProductController::class ,'store'])->name('products.store');
        Route::put('products/{slug}', [ProductController::class ,'update'])->name('products.update');
        Route::delete('products/{slug}', [ProductController::class ,'destroy'])->name('products.destroy');
        
        
        Route::post('categories', [CategoryController::class ,'store'])->name('categories.store');
        Route::put('categories/{slug}', [CategoryController::class ,'update'])->name('categories.update');
        Route::delete('categories/{slug}', [CategoryController::class ,'destroy'])->name('categories.destroy');
    });
        
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
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
        Route::get('/cancel', [PaymentController::class, 'calcel'])->name('cancel');
    });
    
});
