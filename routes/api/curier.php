<?php

use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'can:courier', 'verified'])->prefix('curier')->group(function(){
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assignCourier'])->name('courier.tasks.assignCourier');
    Route::post('/tasks/{task}/unassign', [TaskController::class, 'unassignCourier'])->name('courier.tasks.unassignCourier');
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('courier.tasks.updateStatus');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('curier.tasks.show');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'completeTask'])->name('courier.tasks.completeTask');
    Route::post('/tasks/{task}/decline', [TaskController::class, 'declineTask'])->name('courier.tasks.declineTask');
    Route::post('/tasks/{task}/accept', [TaskController::class, 'acceptTask'])->name('courier.tasks.acceptTask');
});
