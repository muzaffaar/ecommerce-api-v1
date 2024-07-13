<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
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
});