<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function(){
    
    Route::middleware(['can:admin'])->prefix('admin')->group(function(){
        Route::post('products', [ProductController::class ,'store'])->name('products.store');
        Route::put('products/{slug}', [ProductController::class ,'update'])->name('products.update');
        Route::delete('products/{slug}', [ProductController::class ,'destroy'])->name('products.destroy');
        Route::get('products', [ProductController::class ,'index'])->name('products.index');
        Route::get('products/{slug}', [ProductController::class ,'show'])->name('products.show');
        
        
        Route::post('categories', [CategoryController::class ,'store'])->name('categories.store');
        Route::put('categories/{slug}', [CategoryController::class ,'update'])->name('categories.update');
        Route::delete('categories/{slug}', [CategoryController::class ,'destroy'])->name('categories.destroy');
        Route::get('categories', [CategoryController::class ,'index'])->name('categories.index');
        Route::get('categories/{slug}', [CategoryController::class ,'show'])->name('categories.show');
    });
});