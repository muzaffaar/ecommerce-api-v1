<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ReviewController;

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
    
    Route::delete('/delete-item/{itemId}', [CartController::class, 'cartDeleteItem'])->name('carts.delete-item');

    Route::delete('/cart-delete', [CartController::class, 'cartDelete'])->name('carts.delete');
    
    Route::put('/update-item/{itemId}', [CartController::class, 'cartUpdateItem'])->name('carts.update-item');
    
    Route::get('/show-all-items', [CartController::class, 'cartShow'])->name('carts.show-all-items');
});

Route::get('reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');