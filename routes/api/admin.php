<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StatisticsController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum', 'can:admin'])->prefix('admin')->group(function(){
    Route::post('products', [ProductController::class ,'store'])->name('admin.products.store');
    Route::put('products/{slug}', [ProductController::class ,'update'])->name('admin.products.update');
    Route::delete('products/{slug}', [ProductController::class ,'destroy'])->name('admin.products.destroy');
    Route::get('products', [ProductController::class ,'index'])->name('admin.products.index');
    Route::get('products/{slug}', [ProductController::class ,'show'])->name('admin.products.show');
    
    
    Route::post('categories', [CategoryController::class ,'store'])->name('admin.categories.store');
    Route::put('categories/{slug}', [CategoryController::class ,'update'])->name('admin.categories.update');
    Route::delete('categories/{slug}', [CategoryController::class ,'destroy'])->name('admin.categories.destroy');
    Route::get('categories', [CategoryController::class ,'index'])->name('admin.categories.index');
    Route::get('categories/{slug}', [CategoryController::class ,'show'])->name('admin.categories.show');

    Route::post('/tasks/{task}/assign', [TaskController::class, 'assignCourier'])->name('admin.tasks.assignCourier');
    Route::post('/tasks/{task}/unassign', [TaskController::class, 'unassignCourier'])->name('admin.tasks.unassignCourier');
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('admin.tasks.updateStatus');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('admin.tasks.show');
    Route::get('/tasks', [TaskController::class, 'index'])->name('admin.tasks.index');

    Route::get('/dashboard', [StatisticsController::class, 'index'])->name('admin.dashboard');

    Route::post('/coupons', [CouponController::class, 'createCoupon'])->name('admin.coupons.create');
    Route::post('/users/{user}/coupons', [CouponController::class, 'assignCouponToUser'])->name('admin.coupons.assign');

    Route::get('/tags', [TagController::class, 'index'])->name('admin.tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('admin.tags.store');
    Route::get('/tags/{tag}', [TagController::class, 'show'])->name('admin.tags.show');
    Route::put('/tags/{tag}', [TagController::class, 'update'])->name('admin.tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('admin.tags.destroy');
    Route::post('/products/{product}/tags', [TagController::class, 'attachTagToProduct'])->name('admin.tags.attachTagToProduct');
    Route::delete('/products/{product}/tags', [TagController::class, 'detachTagFromProduct'])->name('admin.tags.detachTagFromProduct');
});